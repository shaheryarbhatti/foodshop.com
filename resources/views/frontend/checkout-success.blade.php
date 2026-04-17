@extends('layouts.frontend')

@section('title', __('frontend_order_success'))

@section('styles')
<style>
.success-page { padding: 80px 0; min-height: 70vh; background: #fafafa; display: flex; align-items: center; justify-content: center; }
.success-card { background: #fff; padding: 48px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.06); text-align: center; max-width: 560px; width: 100%; border: 1px solid #f1f1f1; }
.success-icon { width: 80px; height: 80px; border-radius: 50%; background: #e8f5e9; color: #4caf50; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 24px; }
.success-heading { font-weight: 900; font-size: 2rem; color: #1a1a1a; margin-bottom: 12px; }
.success-copy { font-size: 1.1rem; color: #555; margin-bottom: 30px; line-height: 1.6; }
.order-ref { display: inline-block; padding: 8px 16px; background: #f5f5f5; border-radius: 8px; font-family: monospace; font-weight: 700; color: #333; margin-top: 12px; }
.success-actions { display:flex; justify-content:center; flex-wrap:wrap; gap:14px; }
.btn-back-home { display: inline-flex; align-items: center; justify-content: center; padding: 14px 28px; background: #222; color: #fff; border-radius: 12px; font-weight: 800; font-size: 1.05rem; text-decoration: none; transition: background 0.2s; }
.btn-back-home:hover { background: #000; color: #fff; }
.btn-dashboard { display:inline-flex; align-items:center; justify-content:center; padding:14px 28px; background:linear-gradient(135deg,#cb2b1d,#ff9f43); color:#fff; border-radius:12px; font-weight:800; font-size:1.05rem; text-decoration:none; box-shadow:0 14px 26px rgba(15,23,42,.12); }
.btn-dashboard:hover { color:#fff; filter:brightness(.98); }
</style>
@endsection

@section('content')
<section class="success-page">
    <div class="container">
        <div class="success-card mx-auto">
            <div class="success-icon"><i class="fa-solid fa-check"></i></div>
            <h1 class="success-heading">{{ __('frontend_thank_you_order') }}</h1>
            <p class="success-copy">
                {{ __('frontend_order_success_message') }}
                <br>
                <span class="order-ref">{{ __('frontend_order_reference') }}: #{{ $successOrderId ?? session('order_id') ?? rand(1000, 9999) }}</span>
            </p>
            <div class="success-actions">
                @if($hasFrontendCustomer)
                    <a href="{{ route('frontend.dashboard') }}" class="btn-dashboard">Go To Dashboard</a>
                @endif
                <a href="{{ route('frontend.home') }}" class="btn-back-home">{{ __('frontend_back_to_shop') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(() => {
    // Clear the cart on successful checkout
    const storageKey = 'foodshop_frontend_cart';
    sessionStorage.removeItem(storageKey);
})();
</script>
@endsection
