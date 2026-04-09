@extends('layouts.frontend')

@php
    $activeNav = 'cart';
    $currencyRate = max((float) ($currentCurrency?->exchange_rate ?? 1), 1);
    $currencySymbol = $currentCurrency?->symbol ?: '$';
    $fallbackImage = asset('public/assets/images/login/login_image.jpg');
    $frontendViewCartButtonColor = \App\Models\Setting::get('frontend_view_cart_button_color', '#ffc933');
    $frontendViewCartButtonTextColor = \App\Models\Setting::get('frontend_view_cart_button_text_color', '#161616');
    $frontendCheckoutButtonColor = \App\Models\Setting::get('frontend_checkout_button_color', '#151515');
    $frontendCheckoutButtonTextColor = \App\Models\Setting::get('frontend_checkout_button_text_color', '#ffffff');
@endphp

@section('title', __('frontend_view_shopping_cart'))

@section('styles')
<style>
:root{--frontend-view-cart-btn-bg:{{ $frontendViewCartButtonColor }};--frontend-view-cart-btn-text:{{ $frontendViewCartButtonTextColor }};--frontend-checkout-btn-bg:{{ $frontendCheckoutButtonColor }};--frontend-checkout-btn-text:{{ $frontendCheckoutButtonTextColor }}}.cart-page{padding:28px 0 44px}.cart-shell{padding:18px;border-radius:32px;background:rgba(117,117,117,.56);box-shadow:0 28px 70px rgba(0,0,0,.26);backdrop-filter:blur(8px)}.cart-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:18px;align-items:stretch;margin-bottom:18px}.cart-hero-card,.cart-summary-card,.cart-item-card,.cart-empty-card{background:rgba(255,255,255,.96);box-shadow:0 16px 36px rgba(15,23,42,.12)}.cart-hero-card{padding:30px 30px 28px;border-radius:24px}.cart-kicker{display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border-radius:999px;background:rgba(255,202,39,.18);color:#8a4d00;font-size:.8rem;font-weight:900;letter-spacing:.18em;text-transform:uppercase}.cart-heading{margin:18px 0 12px;font-size:clamp(2.2rem,4vw,3.4rem);font-weight:900;line-height:1.02;color:#101828}.cart-copy{max-width:640px;margin:0;color:#475467;font-size:1.03rem;line-height:1.7}.cart-highlight-card{padding:24px;border-radius:24px;background:linear-gradient(135deg,rgba(17,17,17,.94),rgba(37,37,37,.92));color:#fff;position:relative;overflow:hidden}.cart-highlight-card::before{content:"";position:absolute;inset:auto -80px -90px auto;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(255,202,39,.3),transparent 68%)}.cart-highlight-kicker{font-size:.82rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.72)}.cart-highlight-total{margin:16px 0 8px;font-size:clamp(2.4rem,4vw,3rem);font-weight:900;line-height:1}.cart-highlight-copy{margin:0;color:rgba(255,255,255,.76);line-height:1.7}.cart-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(320px,.85fr);gap:18px;align-items:start}.cart-stack{display:flex;flex-direction:column;gap:16px}.cart-item-card{display:grid;grid-template-columns:160px minmax(0,1fr);gap:18px;padding:18px;border-radius:24px}.cart-item-image{width:100%;height:148px;border-radius:18px;object-fit:cover;box-shadow:0 12px 24px rgba(15,23,42,.16)}.cart-item-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.cart-item-title{margin:0;font-size:1.25rem;font-weight:900;color:#111827}.cart-item-subtitle{margin:6px 0 0;color:#667085;font-size:.95rem}.cart-remove-btn{width:42px;height:42px;border:0;border-radius:14px;background:#eb3348;color:#fff;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 12px 22px rgba(235,51,72,.22)}.cart-addon-list{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0 0;padding:0;list-style:none}.cart-addon-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#fff4cc;color:#5f4300;font-size:.87rem;font-weight:800}.cart-addon-chip i{font-size:.72rem}.cart-remarks{margin-top:14px;padding:14px 16px;border-radius:16px;background:#f8fafc;color:#475467;line-height:1.6}.cart-item-footer{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:18px;padding-top:18px;border-top:1px solid rgba(15,23,42,.10)}.cart-qty-control{display:inline-flex;align-items:center;gap:10px;padding:6px;border-radius:16px;background:#fff;border:1px solid rgba(15,23,42,.10);box-shadow:inset 0 1px 0 rgba(255,255,255,.75)}.cart-qty-btn{width:42px;height:42px;border:0;border-radius:12px;background:#f4f6f8;color:#111827;font-size:1.05rem;font-weight:900}.cart-qty-value{min-width:34px;text-align:center;font-size:1.05rem;font-weight:900;color:#111827}.cart-item-price{text-align:right}.cart-unit-price{display:block;color:#667085;font-size:.92rem}.cart-line-total{display:block;color:#101828;font-size:1.25rem;font-weight:900}.cart-summary-card{position:sticky;top:98px;padding:26px;border-radius:24px}.cart-summary-title{margin:0 0 18px;font-size:1.5rem;font-weight:900;color:#111827}.cart-summary-block{padding:18px;border-radius:20px;background:linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid rgba(15,23,42,.08)}.cart-summary-line{display:flex;justify-content:space-between;gap:16px;margin-bottom:14px;color:#344054;font-size:1rem}.cart-summary-line:last-child{margin-bottom:0}.cart-summary-total{margin-top:18px;padding-top:18px;border-top:1px solid rgba(15,23,42,.10);font-size:1.1rem;font-weight:900;color:#101828}.cart-summary-note{margin:18px 0 0;color:#667085;line-height:1.7}.cart-summary-actions{display:grid;gap:12px;margin-top:22px}.cart-summary-actions a{display:flex;align-items:center;justify-content:center;min-height:54px;padding:14px 18px;border-radius:16px;text-decoration:none!important;font-size:1rem;font-weight:900;box-shadow:0 12px 24px rgba(15,23,42,.12);transition:transform .18s ease,box-shadow .18s ease}.cart-summary-actions a:hover{transform:translateY(-1px);box-shadow:0 16px 32px rgba(15,23,42,.16)}.cart-summary-actions .secondary{background:linear-gradient(135deg,var(--frontend-view-cart-btn-bg),var(--frontend-view-cart-btn-bg));color:var(--frontend-view-cart-btn-text)}.cart-summary-actions .primary{background:linear-gradient(135deg,var(--frontend-checkout-btn-bg),var(--frontend-checkout-btn-bg));color:var(--frontend-checkout-btn-text)}.cart-empty-card{padding:34px 26px;border-radius:24px;text-align:center}.cart-empty-icon{width:84px;height:84px;margin:0 auto 18px;border-radius:24px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(255,202,39,.28),rgba(255,202,39,.12));color:#9a5d00;font-size:2rem}.cart-empty-title{margin:0 0 10px;font-size:1.6rem;font-weight:900;color:#101828}.cart-empty-copy{margin:0 auto 18px;max-width:520px;color:#667085;line-height:1.75}.cart-empty-action{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 20px;border-radius:16px;background:linear-gradient(135deg,var(--frontend-view-cart-btn-bg),var(--frontend-view-cart-btn-bg));color:var(--frontend-view-cart-btn-text);font-weight:900;text-decoration:none!important}.cart-muted{color:#667085}@media (max-width:991.98px){.cart-hero,.cart-grid{grid-template-columns:1fr}.cart-summary-card{position:static}}@media (max-width:767.98px){.cart-page{padding-top:18px}.cart-shell{padding:12px;border-radius:22px}.cart-hero-card,.cart-highlight-card,.cart-summary-card,.cart-item-card,.cart-empty-card{border-radius:20px}.cart-hero-card{padding:24px 20px}.cart-item-card{grid-template-columns:1fr}.cart-item-image{height:210px}.cart-item-footer{flex-direction:column;align-items:stretch}.cart-item-price{text-align:left}.cart-summary-card{padding:20px}}
</style>
<style>
    .cart-min-order-note {
        margin-top: 14px;
        padding: 14px 18px;
        border-radius: 16px;
        background: rgba(255, 236, 227, 0.95);
        color: #ad2c1d;
        font-weight: 700;
        text-align: center;
        border: 1px solid rgba(210, 118, 84, 0.6);
        box-shadow: 0 12px 26px rgba(173, 44, 29, 0.12);
        display: block;
    }
</style>
@endsection

@section('content')
<section class="cart-page">
    <div class="container">
        <div class="cart-shell">
            <div class="cart-hero">
                <div class="cart-hero-card">
                    <div class="cart-kicker"><i class="fa-solid fa-bag-shopping"></i> {{ __('frontend_view_shopping_cart') }}</div>
                    <h1 class="cart-heading">{{ __('frontend_cart_page_heading') }}</h1>
                    <p class="cart-copy">{{ __('frontend_cart_page_copy') }}</p>
                </div>
                <div class="cart-highlight-card">
                    <div class="cart-highlight-kicker">{{ __('frontend_order_snapshot') }}</div>
                    <div class="cart-highlight-total" id="cartHeroTotal">{{ $currencySymbol }}0.00</div>
                    <p class="cart-highlight-copy">{{ __('frontend_cart_snapshot_copy') }}</p>
                </div>
            </div>

            <div class="cart-grid">
                <div class="cart-stack" id="cartItemsWrap">
                    <div class="cart-empty-card">
                        <div class="cart-empty-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                        <h2 class="cart-empty-title">{{ __('frontend_basket_empty') }}</h2>
                        <p class="cart-empty-copy">{{ __('frontend_cart_empty_copy') }}</p>
                        <a href="{{ route('frontend.home') }}" class="cart-empty-action">{{ __('frontend_continue_shopping') }}</a>
                    </div>
                </div>

                <aside class="cart-summary-card">
                    <h2 class="cart-summary-title">{{ __('frontend_order_summary') }}</h2>
                    <div class="cart-summary-block" id="cartSummaryWrap">
                        <div class="cart-summary-line">
                            <span>{{ __('frontend_subtotal') }}</span>
                            <span>{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="cart-summary-line">
                            <span>{{ __('frontend_shipping_costs') }}</span>
                            <span>{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="cart-summary-line">
                            <span>{{ __('frontend_vat') }}</span>
                            <span>{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="cart-summary-line cart-summary-total">
                            <span>{{ __('frontend_total') }}</span>
                            <span>{{ $currencySymbol }}0.00</span>
                        </div>
                    </div>
                    <p class="cart-summary-note">{{ __('frontend_cart_summary_note') }}</p>
                    <p class="cart-summary-note cart-min-order-note d-none" id="cartMinOrderNote"></p>
                    <div class="cart-summary-actions">
                        <a href="{{ route('frontend.checkout') }}" class="primary" id="btnCartToCheckout">{{ __('frontend_to_checkout') }}</a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(() => {
const storageKey = 'foodshop_frontend_cart';
const currencyRate = {{ json_encode($currencyRate) }};
const currencySymbol = @json($currencySymbol);
const fallbackImage = @json($fallbackImage);
const activeTax = @json($activeTax ? ['title' => $activeTax->title, 'amount' => (float) $activeTax->amount, 'calculation_type' => $activeTax->calculation_type] : null);
const activeShippingFee = @json($activeShippingFee ? ['fee' => (float) $activeShippingFee->fee] : null);
const labels = {
    subtotal: @json(__('frontend_subtotal')),
    shippingCosts: @json(__('frontend_shipping_costs')),
    vat: @json(__('frontend_vat')),
    total: @json(__('frontend_total')),
    remarks: @json(__('frontend_further_remarks')),
    item: @json(__('item')),
    basketEmpty: @json(__('frontend_basket_empty')),
    cartEmptyCopy: @json(__('frontend_cart_empty_copy')),
    continueShopping: @json(__('frontend_continue_shopping')),
    shipSoon: @json(__('frontend_ship_as_soon_as_possible'))
};
const cartItemsWrap = document.getElementById('cartItemsWrap');
const cartSummaryWrap = document.getElementById('cartSummaryWrap');
const cartHeroTotal = document.getElementById('cartHeroTotal');
const cartMinOrderNote = document.getElementById('cartMinOrderNote');
const cartCheckoutButton = document.getElementById('btnCartToCheckout');
const cartBadge = document.querySelector('.portal-cart-badge');
const cartDeliverySummaryUrl = @json(route('frontend.checkout.delivery-summary'));
const csrfToken = @json(csrf_token());
const minOrderMessageTemplate = @json(__('frontend_min_order_amount_note'));
let cart = loadCart();

function money(value){return currencySymbol + Number(value / currencyRate).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}
function loadCart(){try{return JSON.parse(sessionStorage.getItem(storageKey)) || []}catch(error){return []}}
function saveCart(){sessionStorage.setItem(storageKey, JSON.stringify(cart))}
function basketCount(){return cart.reduce((sum,item)=>sum + Number(item.quantity || 0),0)}
function updateBadge(){if(cartBadge){cartBadge.textContent = String(basketCount())}}
function normalizeItem(item){
    const quantity = Math.max(1, Number(item.quantity || 1));
    const unitTotal = Number(item.unit_total || 0);
    item.quantity = quantity;
    item.unit_total = unitTotal;
    item.line_total = unitTotal * quantity;
    return item;
}
function emptyMarkup(){
    return `<div class="cart-empty-card"><div class="cart-empty-icon"><i class="fa-solid fa-cart-shopping"></i></div><h2 class="cart-empty-title">${labels.basketEmpty}</h2><p class="cart-empty-copy">${labels.cartEmptyCopy}</p><a href="{{ route('frontend.home') }}" class="cart-empty-action">${labels.continueShopping}</a></div>`;
}
function addonMarkup(addons){
    if(!Array.isArray(addons) || !addons.length){return ''}
    return `<ul class="cart-addon-list">${addons.map((addon)=>`<li class="cart-addon-chip"><i class="fa-solid fa-circle"></i><span>${addon.title}: ${addon.values.map((value)=>value.title).join(', ')}</span></li>`).join('')}</ul>`;
}
function remarksMarkup(item){
    if(!item.remarks){return ''}
    return `<div class="cart-remarks"><strong>${labels.remarks}:</strong> ${item.remarks}</div>`;
}
function itemCard(item, index){
    const safeItem = normalizeItem(item);
    return `<article class="cart-item-card"><img src="${safeItem.image_url || fallbackImage}" alt="${safeItem.title}" class="cart-item-image"><div><div class="cart-item-top"><div><h3 class="cart-item-title">${safeItem.serial_number} ${safeItem.title}</h3><div class="cart-item-subtitle">${safeItem.description || labels.shipSoon}</div></div><button type="button" class="cart-remove-btn js-remove-cart-item" data-cart-index="${index}"><i class="fa-solid fa-xmark"></i></button></div>${addonMarkup(safeItem.addons)}${remarksMarkup(safeItem)}<div class="cart-item-footer"><div class="cart-qty-control"><button type="button" class="cart-qty-btn js-cart-qty" data-action="decrease" data-cart-index="${index}">-</button><span class="cart-qty-value">${safeItem.quantity}</span><button type="button" class="cart-qty-btn js-cart-qty" data-action="increase" data-cart-index="${index}">+</button></div><div class="cart-item-price"><span class="cart-unit-price">${labels.item} ${money(safeItem.unit_total)}</span><span class="cart-line-total">${money(safeItem.line_total)}</span></div></div></div></article>`;
}
function renderSummary(subtotal, shippingAmount, vatAmount, totalAmount){
    const vatDisplay = activeTax && activeTax.calculation_type === 'percentage'
        ? `${money(vatAmount)} (${String(activeTax.amount).replace(/\.00$/,'')}%)`
        : money(vatAmount);
    cartSummaryWrap.innerHTML = `<div class="cart-summary-line"><span>${labels.subtotal}</span><span>${money(subtotal)}</span></div><div class="cart-summary-line"><span>${labels.shippingCosts}</span><span>${money(shippingAmount)}</span></div><div class="cart-summary-line"><span>${labels.vat}</span><span>${vatDisplay}</span></div><div class="cart-summary-line cart-summary-total"><span>${labels.total}</span><span>${money(totalAmount)}</span></div>`;
    cartHeroTotal.textContent = money(totalAmount);
}
async function evaluateCartMinimum(subtotal) {
    if(!cartMinOrderNote || !cartCheckoutButton || subtotal <= 0) {
        cartMinOrderNote?.classList.add('d-none');
        cartCheckoutButton?.classList.remove('disabled');
        cartCheckoutButton?.removeAttribute('aria-disabled');
        cartCheckoutButton && (cartCheckoutButton.style.pointerEvents = '');
        return;
    }

    try {
        const response = await fetch(cartDeliverySummaryUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ subtotal, order_type: 'delivery' })
        });
        const payload = await response.json();
        const minAmount = Number(payload?.summary?.shipping_fee?.min_order_amount || 0);
        const requiredMore = Math.max(minAmount - subtotal, 0);
        if(minAmount > 0 && requiredMore > 0) {
            const message = minOrderMessageTemplate.replace(':amount', money(requiredMore));
            cartMinOrderNote.classList.remove('d-none');
            cartMinOrderNote.textContent = message;
            cartCheckoutButton.classList.add('disabled');
            cartCheckoutButton.setAttribute('aria-disabled', 'true');
            cartCheckoutButton.style.pointerEvents = 'none';
            return;
        }
    } catch (error) {
        // ignore failures, fallback to normal state
    }

    cartMinOrderNote.classList.add('d-none');
    cartCheckoutButton.classList.remove('disabled');
    cartCheckoutButton.removeAttribute('aria-disabled');
    cartCheckoutButton.style.pointerEvents = '';
}
function renderCart(){
    updateBadge();
    if(!cart.length){
        cartItemsWrap.innerHTML = emptyMarkup();
        renderSummary(0, 0, 0, 0);
        return;
    }
    cartItemsWrap.innerHTML = cart.map((item, index) => itemCard(item, index)).join('');
    const subtotal = cart.reduce((sum, item) => sum + Number(normalizeItem(item).line_total), 0);
    const shippingAmount = subtotal > 0 && activeShippingFee ? Number(activeShippingFee.fee) : 0;
    const vatAmount = subtotal > 0 && activeTax ? (activeTax.calculation_type === 'percentage' ? subtotal * (Number(activeTax.amount) / 100) : Number(activeTax.amount)) : 0;
    const totalAmount = subtotal + shippingAmount + vatAmount;
    renderSummary(subtotal, shippingAmount, vatAmount, totalAmount);
    evaluateCartMinimum(subtotal);
}

document.addEventListener('click', (event) => {
    const removeBtn = event.target.closest('.js-remove-cart-item');
    if(removeBtn){
        cart.splice(Number(removeBtn.dataset.cartIndex), 1);
        saveCart();
        renderCart();
        return;
    }

    const qtyBtn = event.target.closest('.js-cart-qty');
    if(!qtyBtn){
        return;
    }

    const index = Number(qtyBtn.dataset.cartIndex);
    const action = qtyBtn.dataset.action;
    if(!cart[index]){
        return;
    }

    const nextQty = action === 'increase'
        ? Number(cart[index].quantity || 1) + 1
        : Math.max(1, Number(cart[index].quantity || 1) - 1);

    cart[index].quantity = nextQty;
    normalizeItem(cart[index]);
    saveCart();
    renderCart();
});

const btnCartToCheckout = document.getElementById('btnCartToCheckout');
if(btnCartToCheckout){
    btnCartToCheckout.addEventListener('click', (e) => {
        if(cart.length === 0){
            e.preventDefault();
            alert(labels.basketEmpty);
        }
    });
}

renderCart();
})();
</script>
@endsection
