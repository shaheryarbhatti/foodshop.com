@extends('layouts.frontend')

@section('title', __('complete_payment'))

@section('styles')
<style>
    .pay-balance-page { padding: 60px 0; background: #f8f9fa; min-height: 80vh; }
    .payment-container { max-width: 650px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.08); }
    .amount-badge { font-size: 2.8rem; font-weight: 800; color: #f97316; margin-bottom: 30px; letter-spacing: -1px; }
    
    .method-selector { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 30px; }
    .method-card { border: 2px solid #eee; border-radius: 16px; padding: 20px; cursor: pointer; transition: 0.3s; text-align: center; }
    .method-card:hover { border-color: #f97316; background: #fffaf0; }
    .method-card.active { border-color: #f97316; background: #fff7ed; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15); }
    .method-card i { font-size: 1.5rem; margin-bottom: 10px; display: block; }
    .method-card .method-title { font-weight: 700; font-size: 0.9rem; }

    .stripe-field { min-height: 52px; padding: 15px; border-radius: 12px; border: 1.5px solid #e5e7eb; background: #fff; margin-bottom: 20px; }
    .stripe-field.StripeElement--focus { border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1); }
    
    .bank-details-box { background: #fefce8; border: 1px dashed #facc15; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    
    .btn-pay { width: 100%; padding: 18px; border-radius: 12px; background: #f97316; color: #fff; font-size: 1.25rem; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; }
    .btn-pay:hover { opacity: 0.95; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2); }
    .btn-pay:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
</style>
@endsection

@section('content')
<section class="pay-balance-page">
    <div class="container text-center">
        <div class="payment-container">
            <h2 class="mb-4">{{ __('additional_payment_required') }}</h2>
            <p class="text-muted mb-1">{{ __('order_number') }}: {{ $order->order_number ?: $order->id }}</p>
            <div class="amount-badge">
                {{ $currency->symbol }}{{ number_format($balance, 2) }}
            </div>

            <form id="paymentForm" method="POST" action="{{ route('frontend.orders.process-balance', $order) }}">
                @csrf
                <input type="hidden" name="payment_method" id="selectedMethodInput" value="">
                <input type="hidden" name="gateway_payment_intent_id" id="gatewayPaymentIntentField">
                
                <h6 class="text-start mb-3">{{ __('select_payment_method') }}</h6>
                <div class="method-selector">
                    @foreach($paymentMethods as $method)
                        <div class="method-card js-method-card {{ $loop->first ? 'active' : '' }}" 
                             data-code="{{ $method['code'] }}" 
                             data-type="{{ $method['type'] }}"
                             data-manual="{{ $method['is_manual'] ? '1' : '0' }}">
                            @if($method['type'] === 'stripe')
                                <i class="fa fa-credit-card"></i>
                            @elseif($method['type'] === 'bank_account')
                                <i class="fa fa-university"></i>
                            @else
                                <i class="fa fa-wallet"></i>
                            @endif
                            <div class="method-title">{{ $method['title'] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Stripe Details Section --}}
                <div id="stripeDetailsSection" class="method-details-section text-start" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('cardholder_name') }}</label>
                        <input type="text" id="cardholderName" class="form-control" value="{{ $order->first_name }} {{ $order->last_name }}">
                    </div>
                    
                    <label class="form-label fw-bold">{{ __('card_details') }}</label>
                    <div id="stripeCardElement" class="stripe-field"></div>
                </div>

                {{-- Bank Details Section --}}
                <div id="bankDetailsSection" class="method-details-section text-start" style="display: none;">
                    @php $bankMethod = collect($paymentMethods)->where('type', 'bank_account')->first(); @endphp
                    @if($bankMethod)
                        <div class="bank-details-box">
                            <p class="mb-2"><strong>{{ __('account_title') }}:</strong> {{ $bankMethod['config']['account_title'] ?? '' }}</p>
                            <p class="mb-2"><strong>{{ __('account_number') }}:</strong> {{ $bankMethod['config']['account_number'] ?? '' }}</p>
                            <p class="mb-2"><strong>{{ __('iban') }}:</strong> {{ $bankMethod['config']['iban'] ?? '' }}</p>
                            <p class="mb-0"><strong>{{ __('branch_name') }}:</strong> {{ $bankMethod['config']['branch_name'] ?? '' }}</p>
                        </div>
                        <p class="small text-muted mb-4">
                            <i class="fa fa-info-circle me-1"></i> {{ __('manual_payment_instruction') }}
                        </p>
                    @endif
                </div>

                <div id="cardErrors" class="text-danger small mb-3 text-start"></div>

                <button type="submit" id="btnSubmit" class="btn-pay shadow-sm mt-2">
                    {{ __('pay_now') }}
                </button>
            </form>
            
            <p id="poweredByLabel" class="mt-4 small text-muted">
                {{ __('secure_payment_guaranteed') }}
            </p>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const methods = @json($paymentMethods);
        const form = document.getElementById('paymentForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const cardErrors = document.getElementById('cardErrors');
        const selectedMethodInput = document.getElementById('selectedMethodInput');
        const signature = '{{ hash_hmac('sha256', $order->id, config('app.key')) }}';

        let stripe = null;
        let cardElement = null;
        let currentMethod = null;

        function switchMethod(methodCode) {
            currentMethod = methods.find(m => m.code === methodCode);
            selectedMethodInput.value = methodCode;

            // Hide all sections
            document.querySelectorAll('.method-details-section').forEach(s => s.style.display = 'none');
            document.getElementById('poweredByLabel').textContent = '{{ __('secure_payment_guaranteed') }}';

            if (currentMethod.type === 'stripe') {
                document.getElementById('stripeDetailsSection').style.display = 'block';
                document.getElementById('poweredByLabel').textContent = '{{ __('secure_payment_powered_by_stripe') }}';
                initializeStripe(currentMethod.public_key);
            } else if (currentMethod.type === 'bank_account') {
                document.getElementById('bankDetailsSection').style.display = 'block';
            }
        }

        function initializeStripe(publicKey) {
            if (stripe) return; // Already initialized
            
            stripe = Stripe(publicKey);
            const elements = stripe.elements();
            cardElement = elements.create('card', {
                style: {
                    base: { fontSize: '16px', color: '#1f2937', fontSmoothing: 'antialiased' }
                }
            });
            cardElement.mount('#stripeCardElement');
        }

        // Method Selection UI Logic
        document.querySelectorAll('.js-method-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.js-method-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                switchMethod(this.getAttribute('data-code'));
            });
        });

        // Initialize first method
        const firstCard = document.querySelector('.js-method-card.active');
        if (firstCard) switchMethod(firstCard.getAttribute('data-code'));

        form.addEventListener('submit', async function(e) {
            if (!currentMethod) return e.preventDefault();
            
            // If manual, just submit
            if (currentMethod.is_manual) {
                btnSubmit.disabled = true;
                btnSubmit.textContent = '{{ __('confirming') }}...';
                return;
            }

            // Stripe Logic
            e.preventDefault();
            btnSubmit.disabled = true;
            btnSubmit.textContent = '{{ __('processing') }}...';
            cardErrors.textContent = '';

            try {
                // 1. Create Intent
                const response = await fetch('{{ route('frontend.orders.balance-intent', $order) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Signature': signature
                    },
                    body: JSON.stringify({
                        payment_method: currentMethod.code
                    })
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Failed to create payment intent');

                // 2. Confirm Payment
                const result = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: document.getElementById('cardholderName').value
                        }
                    }
                });

                if (result.error) {
                    throw new Error(result.error.message);
                }

                if (result.paymentIntent.status === 'succeeded') {
                    document.getElementById('gatewayPaymentIntentField').value = result.paymentIntent.id;
                    form.submit();
                }

            } catch (error) {
                cardErrors.textContent = error.message;
                btnSubmit.disabled = false;
                btnSubmit.textContent = '{{ __('pay_now') }}';
            }
        });
    });
</script>
@endsection
