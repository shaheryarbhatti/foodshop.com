@extends('layouts.frontend')

@php
    $activeNav = 'checkout';
    $currencyRate = max((float) ($currentCurrency?->exchange_rate ?? 1), 1);
    $currencySymbol = $currentCurrency?->symbol ?: '$';
    $primaryColor = \App\Models\Setting::get('frontend_checkout_button_color', '#151515');
    $primaryTextColor = \App\Models\Setting::get('frontend_checkout_button_text_color', '#ffffff');
    $checkoutHighlight = \App\Models\Setting::get('theme_primary', '#ffc933');
    $selectedPaymentCode = old('payment_method', $paymentMethods[0]['code'] ?? null);
    $initialDeliverySummary = $deliverySummary ?? ['shipping_cost' => 0, 'distance_km' => null, 'branch' => null, 'has_address' => false];

    $customer = $customer ?? null;
@endphp

@section('title', __('frontend_to_checkout'))

@section('styles')
<style>
:root {
    --checkout-highlight: {{ $checkoutHighlight }};
    --checkout-primary-bg: {{ $primaryColor }};
    --checkout-primary-text: {{ $primaryTextColor }};
}
.checkout-page { padding: 40px 0; background: #fafafa; min-height: 80vh; }
.checkout-shell { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr); gap: 30px; align-items: start; }
.checkout-card { background: #fff; padding: 32px; border-radius: 18px; box-shadow: 0 10px 28px rgba(0,0,0,0.05); }
.checkout-heading { font-weight: 800; font-size: 1.8rem; margin-bottom: 8px; color: #1a1a1a; }
.checkout-subheading { font-weight: 700; font-size: 1.4rem; margin-bottom: 24px; color: #333; }
.form-group { margin-bottom: 18px; }
.form-label { font-weight: 700; color: #333; font-size: 0.95rem; display: block; margin-bottom: 6px; }
.form-control { border-radius: 10px; padding: 12px 14px; border: 1px solid #ddd; width: 100%; font-size: 1rem; color: #555; }
.form-control:focus { outline: none; border-color: var(--checkout-highlight); box-shadow: 0 0 0 2px rgba(255,201,51,0.2); }
.form-check-label { font-size: 0.95rem; color: #444; }

.order-box { position: sticky; top: 100px; }
.order-overview-table { width: 100%; border-collapse: collapse; margin-top: 14px; margin-bottom: 24px; }
.order-overview-table th, .order-overview-table td { padding: 12px 14px; border: 1px solid #eee; text-align: left; }
.order-overview-table th { background: #222; color: #fff; font-weight: 700; font-size: 0.95rem; }
.order-overview-table td.subtotal-label { font-weight: 700; width: 60%; }
.order-overview-table tr.total-row { background: #222; color: #fff; }
.order-overview-table tr.total-row td { border-color: #333; font-weight: 800; }
.order-overview-table .vat-includes { font-size: 0.7rem; color: #aaa; display: block; margin-top: 4px; font-weight: normal; }

.payment-methods { margin-bottom: 18px; display: flex; flex-direction: column; gap: 12px; }
.payment-method-option {
    border: 1px solid #e9edf4;
    border-radius: 16px;
    padding: 14px 16px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    background: #fff;
}
.payment-method-option.is-active {
    border-color: rgba(255, 201, 51, 0.95);
    box-shadow: 0 12px 28px rgba(255, 201, 51, 0.12);
    transform: translateY(-1px);
}
.payment-method-option-head {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 800;
    color: #1f2937;
}
.payment-method-option-meta {
    margin-top: 6px;
    padding-left: 27px;
    font-size: 0.84rem;
    color: #7a8594;
}
.payment-extra-box {
    border: 1px solid #e8edf5;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff, #fbfcff);
    padding: 18px;
    margin-bottom: 20px;
}
.payment-extra-title {
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 8px;
    color: #1f2937;
}
.payment-extra-copy {
    font-size: 0.92rem;
    color: #607086;
    line-height: 1.6;
}
.payment-bank-grid,
.payment-card-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}
.payment-bank-grid .full-span,
.payment-card-grid .full-span {
    grid-column: 1 / -1;
}
.payment-bank-item {
    border: 1px solid #eef2f7;
    border-radius: 14px;
    padding: 12px 14px;
    background: #fff;
}
.payment-bank-label {
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #7a8594;
    margin-bottom: 5px;
}
.payment-bank-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #172033;
    word-break: break-word;
}
.stripe-field {
    min-height: 48px;
    padding: 13px 14px;
    border-radius: 10px;
    border: 1px solid #ddd;
    background: #fff;
}
.stripe-field.StripeElement--focus {
    border-color: var(--checkout-highlight);
    box-shadow: 0 0 0 2px rgba(255,201,51,0.2);
}
.payment-card-note {
    margin-top: 12px;
    font-size: 0.86rem;
    color: #6a7687;
}
.payment-gateway-warning {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #9a3412;
    font-size: 0.88rem;
    font-weight: 700;
}
.payment-error {
    margin-top: 12px;
    color: #c1121f;
    font-size: 0.88rem;
    font-weight: 700;
}
.terms-box { margin-bottom: 24px; font-size: 0.85rem; color: #666; }
.terms-box a { color: #1456ff; }

.place-order-btn {
    width: 100%;
    padding: 16px;
    border-radius: 10px;
    background: var(--checkout-highlight);
    color: #222;
    font-size: 1.15rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    transition: opacity 0.2s;
}
.place-order-btn:hover { opacity: 0.92; }
.place-order-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

@media (max-width: 991px) {
    .checkout-shell { grid-template-columns: 1fr; }
    .order-box { position: static; }
}
@media (max-width: 767.98px) {
    .payment-bank-grid,
    .payment-card-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<section class="checkout-page">
    <div class="container">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('frontend.checkout.submit') }}" id="checkoutForm">
            @csrf

            <input type="hidden" name="cart_data" id="cartDataField" value="">
            <input type="hidden" name="shipping_costs_calculated" id="shippingCostsField" value="0">
            <input type="hidden" name="vat_amount_calculated" id="vatAmountField" value="0">
            <input type="hidden" name="gateway_payment_intent_id" id="gatewayPaymentIntentField" value="">

            <div class="checkout-shell">
                <div class="checkout-card">
                    <h1 class="checkout-heading">{{ __('frontend_to_checkout') }}</h1>
                    <h2 class="checkout-subheading">{{ __('frontend_billing_address') }}</h2>

                    <div class="form-group">
                        <label class="form-label">{{ __('customer_type') }}</label>
                        <select name="customer_type" id="checkoutCustomerType" class="form-control">
                            <option value="individual" {{ old('customer_type', $customer->customer_type ?? 'individual') === 'individual' ? 'selected' : '' }}>{{ __('individual') }}</option>
                            <option value="company" {{ old('customer_type', $customer->customer_type ?? 'individual') === 'company' ? 'selected' : '' }}>{{ __('company') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_first_name') }}</label>
                        <input type="text" name="first_name" class="form-control" placeholder="{{ __('frontend_enter_first_name') }}" value="{{ old('first_name', $customer->first_name ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_last_name') }}</label>
                        <input type="text" name="last_name" class="form-control" placeholder="{{ __('frontend_enter_last_name') }}" value="{{ old('last_name', $customer->last_name ?? '') }}" required>
                    </div>
                    <div class="form-group" id="checkoutCompanyNameWrap" style="{{ old('customer_type', $customer->customer_type ?? 'individual') === 'company' ? '' : 'display:none;' }}">
                        <label class="form-label">{{ __('frontend_company_name') }} ({{ __('frontend_optional') }})</label>
                        <input type="text" name="company_name" id="checkoutCompanyName" class="form-control" placeholder="{{ __('frontend_enter_company_name') }}" value="{{ old('company_name', $customer->company_name ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_email_address') }}</label>
                        <input type="email" name="email" class="form-control" placeholder="{{ __('frontend_enter_email_address') }}" value="{{ old('email', $customer->email ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_telephone_number') }}</label>
                        <input type="tel" name="phone" class="form-control" placeholder="{{ __('frontend_enter_phone_number') }}" value="{{ old('phone', $customer->phone ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_address') }}</label>
                        <input type="text" name="address" class="form-control" placeholder="{{ __('frontend_enter_address') }}" value="{{ old('address', $customer->address ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_city') }}</label>
                        <input type="text" name="city" class="form-control" placeholder="{{ __('frontend_enter_city') }}" value="{{ old('city', $customer->city ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_postal_code') }}</label>
                        <input type="text" name="postal_code" class="form-control" placeholder="{{ __('frontend_enter_postal_code') }}" value="{{ old('postal_code', $customer->postal_code ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('frontend_country') }}</label>
                        <input type="text" name="country" class="form-control" placeholder="{{ __('frontend_enter_country') }}" value="{{ old('country', $customer->country ?? 'Germany') }}" required>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="differentAddress" name="different_delivery_address" {{ old('different_delivery_address') ? 'checked' : '' }}>
                        <label class="form-check-label ms-1" for="differentAddress">
                            {{ __('frontend_send_to_different_address') }}
                        </label>
                    </div>
                </div>

                <div class="checkout-card order-box">
                    <h2 class="checkout-subheading">{{ __('frontend_your_order') }}</h2>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="order_type" id="delivery" value="delivery" {{ old('order_type', 'delivery') === 'delivery' ? 'checked' : '' }}>
                        <label class="form-check-label ms-1" for="delivery">{{ __('frontend_delivery') }}</label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="order_type" id="pickup" value="pick_up" {{ old('order_type') === 'pick_up' ? 'checked' : '' }}>
                        <label class="form-check-label ms-1" for="pickup">{{ __('frontend_pick_up') }}</label>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('frontend_order_notes') }} ({{ __('frontend_optional') }})</label>
                        <textarea class="form-control" name="order_notes" rows="3" placeholder="{{ __('frontend_order_notes_placeholder') }}">{{ old('order_notes') }}</textarea>
                    </div>

                    <h2 class="checkout-subheading mb-0">{{ __('frontend_order_overview') }}</h2>
                    <table class="order-overview-table">
                        <thead>
                            <tr>
                                <th>{{ __('frontend_product') }}</th>
                                <th>{{ __('frontend_in_total') }}</th>
                            </tr>
                        </thead>
                        <tbody id="checkoutProductsList"></tbody>
                        <tfoot>
                            <tr>
                                <td class="subtotal-label">{{ __('frontend_subtotal') }}</td>
                                <td id="checkoutSubtotal">{{ $currencySymbol }}0.00</td>
                            </tr>
                            <tr id="checkoutShippingRow">
                                <td class="subtotal-label">{{ __('frontend_shipping_costs') }}</td>
                                <td id="checkoutShippingAmount">{{ $currencySymbol }}0.00</td>
                            </tr>
                            <tr class="total-row">
                                <td>{{ __('frontend_in_total') }}</td>
                                <td>
                                    <span id="checkoutTotal">{{ $currencySymbol }}0.00</span>
                                    <span class="vat-includes" id="checkoutVatDisplay">{{ __('frontend_includes_vat') }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <h2 class="checkout-subheading mb-3">{{ __('frontend_payment_method') }}</h2>
                    @if (count($paymentMethods))
                        <div class="payment-methods" id="paymentMethodsList">
                            @foreach ($paymentMethods as $method)
                                <label class="payment-method-option {{ $selectedPaymentCode === $method['code'] ? 'is-active' : '' }}" data-payment-option data-method-code="{{ $method['code'] }}">
                                    <div class="payment-method-option-head">
                                        <input class="form-check-input mt-0" type="radio" name="payment_method" value="{{ $method['code'] }}" {{ $selectedPaymentCode === $method['code'] ? 'checked' : '' }}>
                                        <span>{{ $method['title'] }}</span>
                                    </div>
                                    <div class="payment-method-option-meta">{{ ucfirst(str_replace('_', ' ', $method['type'])) }}</div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="payment-extra-box">
                            <div class="payment-extra-copy">{{ __('frontend_no_payment_methods_available') }}</div>
                        </div>
                    @endif

                    <div class="payment-extra-box" id="paymentDescriptionBox" style="display:none;">
                        <div class="payment-extra-title">{{ __('frontend_payment_description') }}</div>
                        <div class="payment-extra-copy" id="paymentDescriptionText"></div>
                    </div>

                    <div class="payment-extra-box" id="bankDetailsBox" style="display:none;">
                        <div class="payment-extra-title">{{ __('frontend_bank_details') }}</div>
                        <div class="payment-bank-grid" id="bankDetailsGrid"></div>
                    </div>

                    <div class="payment-extra-box" id="cardPaymentBox" style="display:none;">
                        <div class="payment-extra-title">{{ __('frontend_card_payment_details') }}</div>
                        <div class="payment-card-grid">
                            <div class="full-span">
                                <label class="form-label">{{ __('frontend_name_on_card') }}</label>
                                <input type="text" class="form-control" id="cardholderName" placeholder="{{ __('frontend_enter_name_on_card') }}">
                            </div>
                            <div class="full-span">
                                <label class="form-label">{{ __('frontend_card_number') }}</label>
                                <div id="stripeCardNumber" class="stripe-field"></div>
                            </div>
                            <div>
                                <label class="form-label">{{ __('frontend_expiry_date') }}</label>
                                <div id="stripeCardExpiry" class="stripe-field"></div>
                            </div>
                            <div>
                                <label class="form-label">{{ __('frontend_cvv') }}</label>
                                <div id="stripeCardCvc" class="stripe-field"></div>
                            </div>
                        </div>
                        <div class="payment-card-note">{{ __('frontend_payment_card_note') }}</div>
                        <div class="payment-gateway-warning d-none" id="paymentGatewayWarning">{{ __('frontend_payment_gateway_setup_required') }}</div>
                        <div class="payment-error {{ $errors->has('payment_method') ? '' : 'd-none' }}" id="paymentErrorBox">{{ $errors->first('payment_method') }}</div>
                    </div>

                    <div class="terms-box mt-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label ms-1" for="terms">
                                {{ __('frontend_i_have_read_accepted') }} <a href="#">{{ __('frontend_terms_conditions') }}</a>, {{ __('frontend_the') }} <a href="#">{{ __('frontend_privacy_policy') }}</a>, {{ __('frontend_and_the') }} <a href="#">{{ __('frontend_cancellation_policy') }}</a>. *
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dataTransfer" required>
                            <label class="form-check-label ms-1" for="dataTransfer">
                                {{ __('frontend_agree_data_transferred') }} *
                            </label>
                        </div>
                    </div>

                    <p class="text-danger small mt-2 d-none" id="checkoutMinOrderNote"></p>
                    <button type="submit" class="place-order-btn" id="placeOrderButton" {{ count($paymentMethods) ? '' : 'disabled' }}>{{ __('frontend_place_order') }}</button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(() => {
const storageKey = 'foodshop_frontend_cart';
const stripeIntentStorageKey = 'foodshop_frontend_stripe_intent';
const cartDataField = document.getElementById('cartDataField');
const shippingFields = document.getElementById('shippingCostsField');
const vatField = document.getElementById('vatAmountField');
const gatewayPaymentIntentField = document.getElementById('gatewayPaymentIntentField');
const checkoutForm = document.getElementById('checkoutForm');
const checkoutCustomerType = document.getElementById('checkoutCustomerType');
const checkoutCompanyNameWrap = document.getElementById('checkoutCompanyNameWrap');
const checkoutCompanyName = document.getElementById('checkoutCompanyName');
const placeOrderButton = document.getElementById('placeOrderButton');
const paymentDescriptionBox = document.getElementById('paymentDescriptionBox');
const paymentDescriptionText = document.getElementById('paymentDescriptionText');
const bankDetailsBox = document.getElementById('bankDetailsBox');
const bankDetailsGrid = document.getElementById('bankDetailsGrid');
const cardPaymentBox = document.getElementById('cardPaymentBox');
const paymentErrorBox = document.getElementById('paymentErrorBox');
const paymentGatewayWarning = document.getElementById('paymentGatewayWarning');

const currencyRate = {{ json_encode($currencyRate) }};
const currencySymbol = @json($currencySymbol);
const activeTax = @json($activeTax ? ['title' => $activeTax->title, 'amount' => (float) $activeTax->amount, 'calculation_type' => $activeTax->calculation_type] : null);
let deliverySummary = @json($initialDeliverySummary);
const paymentMethods = @json($paymentMethods);
const paymentIntentUrl = @json(route('frontend.checkout.payment-intent'));
const deliverySummaryUrl = @json(route('frontend.checkout.delivery-summary'));
const csrfToken = @json(csrf_token());
const stripeCountryMap = @json($checkoutCountryCodes);
const checkoutMinOrderNote = document.getElementById('checkoutMinOrderNote');
const minOrderMessageTemplate = @json(__('frontend_min_order_amount_note'));
const i18n = {
    basketEmpty: @json(__('frontend_basket_empty')),
    selectMethod: @json(__('frontend_select_payment_method_first')),
    accountTitle: @json(__('payment_method_account_title')),
    iban: @json(__('payment_method_iban')),
    branchName: @json(__('payment_method_branch_name')),
    accountNumber: @json(__('payment_method_account_number')),
    cardNameMissing: @json(__('frontend_name_on_card_required')),
    processing: @json(__('frontend_payment_processing')),
    genericPaymentError: @json(__('frontend_payment_failed_generic')),
};

function money(value){ return currencySymbol + Number(value / currencyRate).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function loadCart(){ try{ return JSON.parse(sessionStorage.getItem(storageKey)) || []; }catch(error){ return []; } }
function saveStripeIntent(methodCode, paymentIntentId){ try{ sessionStorage.setItem(stripeIntentStorageKey, JSON.stringify({ method_code: methodCode, payment_intent_id: paymentIntentId || '' })); }catch(error){} }
function loadStripeIntent(){ try{ return JSON.parse(sessionStorage.getItem(stripeIntentStorageKey)) || null; }catch(error){ return null; } }
function clearStripeIntent(){ try{ sessionStorage.removeItem(stripeIntentStorageKey); }catch(error){} }
function resolveStripeCountryCode(countryValue){const raw=(countryValue||'').trim();if(!raw){return undefined}if(/^[A-Za-z]{2}$/.test(raw)){return raw.toUpperCase()}const mapped=stripeCountryMap[raw.toLowerCase()];return mapped||undefined}
function syncCustomerTypeUI() {
    if (!checkoutCustomerType || !checkoutCompanyNameWrap) {
        return;
    }

    const isCompany = checkoutCustomerType.value === 'company';
    checkoutCompanyNameWrap.style.display = isCompany ? '' : 'none';

    if (!isCompany && checkoutCompanyName) {
        checkoutCompanyName.value = '';
    }
}
function showPaymentError(message) {
    if (!paymentErrorBox) return;
    paymentErrorBox.textContent = message || i18n.genericPaymentError;
    paymentErrorBox.classList.remove('d-none');
}
function clearPaymentError() {
    if (!paymentErrorBox) return;
    paymentErrorBox.textContent = '';
    paymentErrorBox.classList.add('d-none');
}
function showGatewayWarning(show, message = '') {
    if (!paymentGatewayWarning) return;
    paymentGatewayWarning.textContent = message || @json(__('frontend_payment_gateway_setup_required'));
    paymentGatewayWarning.classList.toggle('d-none', !show);
}

let cart = loadCart();
let stripe = null;
let elements = null;
let cardNumberElement = null;
let cardExpiryElement = null;
let cardCvcElement = null;
let subtotal = 0;
let shippingAmount = 0;
let vatAmount = 0;
let totalAmount = 0;
let checkoutMinOrderBlocked = false;
let isProcessing = false;
let stripeSubmissionApproved = false;

if(cart.length === 0){
    alert(i18n.basketEmpty);
    window.location.href = @json(route('frontend.cart'));
}

function selectedOrderType() {
    return document.getElementById('pickup').checked ? 'pick_up' : 'delivery';
}

async function refreshDeliverySummary() {
    try {
        const response = await fetch(deliverySummaryUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                subtotal,
                order_type: selectedOrderType(),
            })
        });

        const payload = await response.json();
        deliverySummary = payload.summary || deliverySummary;
    } catch (error) {
        deliverySummary = deliverySummary || { shipping_cost: 0 };
    }

    updateMinimumOrderWarning();
}

function refreshPlaceOrderButtonState() {
    if (!placeOrderButton) {
        return;
    }
    placeOrderButton.disabled = checkoutMinOrderBlocked || isProcessing || paymentMethods.length === 0;
}

function setMinimumOrderBlock(block) {
    checkoutMinOrderBlocked = block;
    refreshPlaceOrderButtonState();
}

function updateMinimumOrderWarning() {
    if (!checkoutMinOrderNote) {
        return;
    }

    const minAmount = Number(deliverySummary?.shipping_fee?.min_order_amount ?? 0);
    const meetsRequirement = totalAmount >= minAmount;

    if (minAmount > 0 && !meetsRequirement) {
        checkoutMinOrderNote.textContent = minOrderMessageTemplate.replace(':amount', money(minAmount));
        checkoutMinOrderNote.classList.remove('d-none');
        setMinimumOrderBlock(true);
        return;
    }

    checkoutMinOrderNote.classList.add('d-none');
    setMinimumOrderBlock(false);
}

async function recalculateTotals() {
    subtotal = 0;
    let productRows = '';

    cart.forEach(item => {
        subtotal += Number(item.line_total || 0);
        productRows += `<tr>
            <td style="color:#555;">${item.quantity} × ${item.title}</td>
            <td style="font-weight:700;">${money(item.line_total)}</td>
        </tr>`;
    });

    document.getElementById('checkoutProductsList').innerHTML = productRows;
    await refreshDeliverySummary();
    shippingAmount = selectedOrderType() === 'pick_up' ? 0 : Number(deliverySummary.shipping_cost || 0);
    vatAmount = subtotal > 0 && activeTax ? (activeTax.calculation_type === 'percentage' ? subtotal * (Number(activeTax.amount) / 100) : Number(activeTax.amount)) : 0;
    totalAmount = subtotal + shippingAmount + vatAmount;

    document.getElementById('checkoutSubtotal').textContent = money(subtotal);
    document.getElementById('checkoutShippingAmount').textContent = money(shippingAmount);
    document.getElementById('checkoutTotal').textContent = money(totalAmount);

    const vatText = activeTax && activeTax.calculation_type === 'percentage'
        ? `{{ __('frontend_includes') }} ${money(vatAmount)} {{ __('frontend_vat') }} (${String(activeTax.amount).replace(/\.00$/, '')}%)`
        : `{{ __('frontend_includes') }} ${money(vatAmount)} {{ __('frontend_vat') }}`;

    document.getElementById('checkoutVatDisplay').textContent = vatAmount > 0 || activeTax ? vatText : '';
    document.getElementById('checkoutShippingRow').style.display = selectedOrderType() === 'pick_up' ? 'none' : 'table-row';
    shippingFields.value = shippingAmount;
    vatField.value = vatAmount;
    updateMinimumOrderWarning();
    refreshPlaceOrderButtonState();
}

function getSelectedPaymentMethod() {
    const selected = checkoutForm.querySelector('input[name="payment_method"]:checked');
    if (!selected) return null;
    return paymentMethods.find((method) => method.code === selected.value) || null;
}

function destroyStripeElements() {
    if (cardNumberElement) { cardNumberElement.unmount(); cardNumberElement = null; }
    if (cardExpiryElement) { cardExpiryElement.unmount(); cardExpiryElement = null; }
    if (cardCvcElement) { cardCvcElement.unmount(); cardCvcElement = null; }
    elements = null;
    stripe = null;
}

function mountStripeFields(publicKey) {
    if (!publicKey || typeof Stripe === 'undefined') {
        destroyStripeElements();
        return;
    }

    destroyStripeElements();
    stripe = Stripe(publicKey);
    elements = stripe.elements({
        appearance: {
            theme: 'stripe',
            variables: {
                colorPrimary: '#f2b405',
                colorBackground: '#ffffff',
                colorText: '#1f2937',
                colorDanger: '#c1121f',
                fontFamily: 'Nunito Sans, sans-serif',
                borderRadius: '10px'
            }
        }
    });

    cardNumberElement = elements.create('cardNumber');
    cardExpiryElement = elements.create('cardExpiry');
    cardCvcElement = elements.create('cardCvc');

    cardNumberElement.mount('#stripeCardNumber');
    cardExpiryElement.mount('#stripeCardExpiry');
    cardCvcElement.mount('#stripeCardCvc');
}

function renderBankDetails(method) {
    if (!bankDetailsGrid) return;
    const details = method.bank_details || {};
    const rows = [
        { label: i18n.accountTitle, value: details.account_title || '-' },
        { label: i18n.iban, value: details.iban || '-' },
        { label: i18n.branchName, value: details.branch_name || '-' },
        { label: i18n.accountNumber, value: details.account_number || '-' },
    ];

    bankDetailsGrid.innerHTML = rows.map((row) => `
        <div class="payment-bank-item">
            <div class="payment-bank-label">${row.label}</div>
            <div class="payment-bank-value">${row.value}</div>
        </div>
    `).join('');
}

function updatePaymentPanels() {
    const method = getSelectedPaymentMethod();
    clearPaymentError();
    gatewayPaymentIntentField.value = '';
    clearStripeIntent();

    document.querySelectorAll('[data-payment-option]').forEach((option) => {
        option.classList.toggle('is-active', option.dataset.methodCode === (method ? method.code : ''));
    });

    if (!method) {
        if (paymentDescriptionBox) paymentDescriptionBox.style.display = 'none';
        if (bankDetailsBox) bankDetailsBox.style.display = 'none';
        if (cardPaymentBox) cardPaymentBox.style.display = 'none';
        showGatewayWarning(false);
        destroyStripeElements();
        return;
    }

    if (paymentDescriptionBox) {
        const description = method.description || '';
        paymentDescriptionText.textContent = description;
        paymentDescriptionBox.style.display = description ? 'block' : 'none';
    }

    if (method.type === 'bank_account') {
        renderBankDetails(method);
        bankDetailsBox.style.display = 'block';
    } else {
        bankDetailsBox.style.display = 'none';
    }

    if (method.requires_card_form && method.public_key) {
        cardPaymentBox.style.display = 'block';
        showGatewayWarning(false);
        mountStripeFields(method.public_key);
    } else if (method.requires_card_form) {
        cardPaymentBox.style.display = 'block';
        showGatewayWarning(true);
        destroyStripeElements();
    } else {
        cardPaymentBox.style.display = 'none';
        showGatewayWarning(false);
        destroyStripeElements();
    }
}

async function createStripePaymentIntent() {
    const method = getSelectedPaymentMethod();
    if (!method) {
        showPaymentError(i18n.selectMethod);
        return null;
    }

    const cardholderName = document.getElementById('cardholderName')?.value?.trim() || '';
    if (!cardholderName) {
        showPaymentError(i18n.cardNameMissing);
        return null;
    }

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('first_name', checkoutForm.querySelector('input[name="first_name"]').value);
    formData.append('last_name', checkoutForm.querySelector('input[name="last_name"]').value);
    formData.append('email', checkoutForm.querySelector('input[name="email"]').value);
    formData.append('phone', checkoutForm.querySelector('input[name="phone"]').value);
    formData.append('order_type', selectedOrderType());
    formData.append('payment_method', method.code);
    formData.append('cart_data', JSON.stringify(cart));

    const response = await fetch(paymentIntentUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    });

    const payload = await response.json();
    if (!response.ok) {
        showPaymentError(payload.message || i18n.genericPaymentError);
        return null;
    }

    return payload;
}

async function confirmStripePayment() {
    const method = getSelectedPaymentMethod();
    if (!method || !method.public_key || !stripe || !cardNumberElement) {
        showPaymentError(@json(__('frontend_payment_method_missing_keys')));
        return false;
    }

    const intent = await createStripePaymentIntent();
    if (!intent || !intent.client_secret) {
        return false;
    }
    if (gatewayPaymentIntentField && intent.payment_intent_id) {
        gatewayPaymentIntentField.value = intent.payment_intent_id;
    }

    const cardholderName = document.getElementById('cardholderName')?.value?.trim() || '';
    const stripeCountryCode = resolveStripeCountryCode(checkoutForm.querySelector('input[name="country"]').value);
    const result = await stripe.confirmCardPayment(intent.client_secret, {
        payment_method: {
            card: cardNumberElement,
            billing_details: {
                name: cardholderName,
                email: checkoutForm.querySelector('input[name="email"]').value,
                phone: checkoutForm.querySelector('input[name="phone"]').value,
                address: {
                    line1: checkoutForm.querySelector('input[name="address"]').value,
                    city: checkoutForm.querySelector('input[name="city"]').value,
                    postal_code: checkoutForm.querySelector('input[name="postal_code"]').value,
                    ...(stripeCountryCode ? { country: stripeCountryCode } : {}),
                }
            }
        }
    });

    if (result.error) {
        showPaymentError(result.error.message || i18n.genericPaymentError);
        return false;
    }

    if (!result.paymentIntent || result.paymentIntent.status !== 'succeeded') {
        showPaymentError(i18n.genericPaymentError);
        return false;
    }

    if (gatewayPaymentIntentField) {
        gatewayPaymentIntentField.value = result.paymentIntent.id || intent.payment_intent_id || '';
    }
    saveStripeIntent(method.code, gatewayPaymentIntentField ? gatewayPaymentIntentField.value : (result.paymentIntent.id || intent.payment_intent_id || ''));
    return true;
}

cartDataField.value = JSON.stringify(cart);
recalculateTotals();
updatePaymentPanels();
syncCustomerTypeUI();

if (checkoutCustomerType) {
    checkoutCustomerType.addEventListener('change', syncCustomerTypeUI);
}

document.querySelectorAll('input[name="order_type"]').forEach((radio) => {
    radio.addEventListener('change', () => { recalculateTotals(); });
});

document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
    radio.addEventListener('change', updatePaymentPanels);
});

checkoutForm.addEventListener('submit', async (event) => {
    if (stripeSubmissionApproved) {
        stripeSubmissionApproved = false;
        return;
    }

    clearPaymentError();

    const method = getSelectedPaymentMethod();
    if (!method) {
        event.preventDefault();
        showPaymentError(i18n.selectMethod);
        return;
    }

    if (method.requires_card_form) {
        event.preventDefault();
    }

    if (gatewayPaymentIntentField && !gatewayPaymentIntentField.value) {
        const storedIntent = loadStripeIntent();
        if (storedIntent && storedIntent.method_code === method.code && storedIntent.payment_intent_id) {
            gatewayPaymentIntentField.value = storedIntent.payment_intent_id;
        }
    }

    cartDataField.value = JSON.stringify(cart);
    await recalculateTotals();

    if (!method.requires_card_form) {
        return;
    }
    isProcessing = true;
    refreshPlaceOrderButtonState();
    placeOrderButton.textContent = i18n.processing;

    const success = await confirmStripePayment();
    if (success) {
        stripeSubmissionApproved = true;
        isProcessing = false;
        refreshPlaceOrderButtonState();
        placeOrderButton.textContent = @json(__('frontend_place_order'));
        if (typeof checkoutForm.requestSubmit === 'function') {
            checkoutForm.requestSubmit();
        } else {
            checkoutForm.submit();
        }
        return;
    }

    isProcessing = false;
    refreshPlaceOrderButtonState();
    placeOrderButton.textContent = @json(__('frontend_place_order'));
});
})();
</script>
@endsection
