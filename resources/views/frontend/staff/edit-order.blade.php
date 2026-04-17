@extends('layouts.frontend')

@section('title', 'Edit Order')

@php
    $activeNav = 'staff-dashboard';
    $userImage = $portalUser->image ? asset('public/storage/' . $portalUser->image) : null;
    $historyEntries = [];
    foreach ($order->items as $item) {
        $additionAmount = (float) ($item->addition_amount ?? 0);
        $additionTax = (float) ($item->addition_tax ?? 0);
        if ($additionAmount <= 0 && $additionTax <= 0) {
            continue;
        }
        $historyEntries[] = ['item' => $item->title, 'amount' => $additionAmount, 'tax' => $additionTax];
    }
@endphp

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
.staff-edit{padding:34px 0 56px}.staff-edit-main{border-radius:28px;background:rgba(255,255,255,.96);border:1px solid rgba(15,23,42,.08);box-shadow:0 24px 60px rgba(15,23,42,.12);padding:28px}.staff-edit-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}.staff-edit-head h1{margin:0;color:#0f172a;font-size:clamp(2rem,3vw,2.5rem);font-weight:900}.staff-edit-head p{margin:10px 0 0;color:#64748b;line-height:1.7;max-width:760px}.staff-edit-actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center}.staff-back-link,.staff-logout-btn{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border-radius:999px;text-decoration:none;font-weight:900;border:0}.staff-back-link{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;box-shadow:0 14px 26px rgba(15,23,42,.16)}.staff-logout-btn{background:linear-gradient(135deg,#cb2b1d,#f08b3f);color:#fff;box-shadow:0 14px 26px rgba(203,43,29,.18)}.staff-card{padding:24px;border-radius:24px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 18px 40px rgba(15,23,42,.06)}.staff-card+.staff-card{margin-top:22px}.staff-card h2,.staff-card h3{margin:0 0 16px;color:#0f172a;font-weight:900}.summary-list{list-style:none;padding:0;margin:0;display:grid;gap:12px}.summary-list li{display:flex;justify-content:space-between;gap:12px;font-weight:700;color:#334155}.summary-list strong{color:#0f172a}.history-box{display:grid;gap:10px}.history-item{padding:14px 16px;border-radius:16px;background:#f8fafc;border:1px solid rgba(15,23,42,.06)}.history-item strong{display:block;color:#0f172a}.history-item span{display:block;color:#64748b;font-size:.9rem}.existing-item{opacity:.9}.staff-edit-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(340px,.8fr);gap:24px}.table td,.table th{vertical-align:middle}.select2-container--bootstrap-5{font-size:1rem}
@media (max-width:991.98px){.staff-edit-grid{grid-template-columns:1fr}}
@media (max-width:767.98px){.staff-edit{padding:18px 0 32px}.staff-edit-main{padding:18px;border-radius:22px}.staff-edit-head{flex-direction:column;align-items:flex-start}}
</style>
@endsection

@section('content')
<section class="staff-edit">
    <div class="container">
        <div class="staff-edit-main">
                <div class="staff-edit-head">
                    <div>
                        <h1>Edit Order {{ $order->order_number ?: ('#' . $order->id) }}</h1>
                        <p>Adjust items, add new products, update addon costs, and review the recalculated balance from the frontend operations panel.</p>
                    </div>
                    <div class="staff-edit-actions">
                        <a href="{{ route('frontend.staff.dashboard') }}" class="staff-back-link">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Back to Orders</span>
                        </a>
                        <form method="POST" action="{{ route('frontend.logout') }}">
                            @csrf
                            <button type="submit" class="staff-logout-btn">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>

                @if($order->admin_updated)
                    <div class="alert alert-warning mt-3">{{ __('order_updated_by_admin_note') }}</div>
                @endif

                <div class="staff-edit-grid mt-4">
                    <div>
                        <div class="staff-card">
                            <h2>{{ __('ordered_items') }}</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('product') }}</th>
                                            <th>{{ __('quantity') }}</th>
                                            <th class="text-end">{{ __('unit_price') }}</th>
                                            <th class="text-end">{{ __('new_item_price') }}</th>
                                            <th class="text-center">{{ __('action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr class="existing-item" data-id="{{ $item->id }}" data-product-id="{{ $item->product_id }}" data-current-addons="{{ json_encode($item->addons) }}">
                                                <td>
                                                    <div class="fw-bold">{{ $item->title }}</div>
                                                    @if($item->addons)
                                                        <div class="small text-muted">
                                                            @foreach($item->addons as $groupOrAddon)
                                                                @if(isset($groupOrAddon['values']) && is_array($groupOrAddon['values']))
                                                                    <span class="badge bg-light text-dark border me-1">{{ $groupOrAddon['title'] }}: {{ collect($groupOrAddon['values'])->pluck('title')->join(', ') }}</span>
                                                                @else
                                                                    <span class="badge bg-light text-dark border me-1">{{ $groupOrAddon['addon_title'] ?? 'Addon' }}: {{ $groupOrAddon['title'] }}</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-end new-addon-price" data-item-id="{{ $item->id }}">{{ number_format($item->addition_amount ?? 0, 2) }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button type="button" class="btn btn-info btn-sm js-edit-existing"><i class="fa fa-edit text-white"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm js-remove-item" data-id="{{ $item->id }}"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="5">
                                                <div class="d-flex gap-2">
                                                    <select id="productSelect" class="form-select">
                                                        <option value="">{{ __('select_products') }}</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-price="{{ $product->base_price }}" data-title="{{ $product->title }}" data-addons="{{ json_encode($product->addons) }}">{{ $product->title }} ({{ number_format((float) $product->base_price, 2) }})</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-dark" id="btnAddItem"><i class="fa fa-plus me-1"></i>{{ __('add') }}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="staff-card">
                            <h3>{{ __('order_summary') }}</h3>
                            <ul class="summary-list">
                                <li><span>{{ __('subtotal') }}</span><strong id="lblSubtotal">{{ number_format($order->subtotal, 2) }}</strong></li>
                                <li><span>{{ __('shipping_costs') }}</span><strong id="lblShipping">{{ number_format($order->shipping_costs, 2) }}</strong></li>
                                <li><span>{{ __('vat_amount') }}</span><strong id="lblVat">{{ number_format($order->vat_amount, 2) }}</strong></li>
                                @if((float) $order->discount_amount > 0)
                                    <li><span>{{ __('coupon_discount') }}{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span><strong id="lblDiscount">-{{ number_format($order->discount_amount, 2) }}</strong></li>
                                @endif
                                <li><span>{{ __('grand_total') }}</span><strong id="lblGrandTotal">{{ number_format($order->grand_total, 2) }}</strong></li>
                                <li><span>{{ __('additional_payment_required') }}</span><strong id="lblAdditionalCharges">0.00</strong></li>
                                <li><span>{{ __('additional_tax') }}</span><strong id="lblAdditionalTax">0.00</strong></li>
                                <li><span>{{ __('new_balance') }}</span><strong id="lblBalance">0.00</strong></li>
                            </ul>
                            <form id="editOrderForm" action="{{ route('frontend.staff.orders.update', $order) }}" method="POST" class="mt-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="items_json" id="itemsJson">
                                <button type="submit" class="btn btn-dark w-100 py-3"><i class="fa fa-save me-2"></i>{{ __('update_order') }}</button>
                            </form>
                        </div>

                        <div class="staff-card">
                            <h3>{{ __('change_history') }}</h3>
                            <div id="historyContainer" class="history-box"></div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addonProductTitle">{{ __('addons') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addonContainer"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                <button type="button" class="btn btn-dark" id="confirmAddons">{{ __('confirm') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productSelect = $('#productSelect').select2({ theme: 'bootstrap-5', width: '100%' });
    let newItems = [], deletedExistingIds = [], existingAddonUpdates = {};
    const originalSubtotal = {{ $order->subtotal }}, originalVatAmount = {{ $order->vat_amount }}, originalGrandTotal = {{ $order->grand_total }};
    const vatRate = parseFloat(@json($activeTax?->amount ?? 0)) || 0;
    const vatType = @json($activeTax?->calculation_type ?? 'percentage');
    const allProducts = @json($products);
    const addonModal = new bootstrap.Modal(document.getElementById('addonModal'));
    const addonContainer = document.getElementById('addonContainer');
    const addonProductTitle = document.getElementById('addonProductTitle');
    let pendingProduct = null, isEditingExisting = false, editingRowId = null;

    const calculateAdditionTax = (amount) => !amount ? 0 : (vatType === 'percentage' ? amount * (vatRate / 100) : vatRate);
    const formatCurrency = (value) => (Number(value) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function renderHistory() {
        const entries = [...@json($historyEntries), ...newItems.map(item => ({ item: item.title, amount: item.addition_amount, tax: item.addition_tax })), ...Object.values(existingAddonUpdates).map(entry => ({ item: entry.title, amount: entry.amount, tax: entry.tax }))];
        document.getElementById('historyContainer').innerHTML = entries.length ? entries.map(entry => `<div class="history-item"><strong>${entry.item}</strong><span>Amount: ${formatCurrency(entry.amount)}</span><span>Tax: ${formatCurrency(entry.tax)}</span></div>`).join('') : `<div class="text-muted small">{{ __('history_empty') }}</div>`;
    }

    function updateTotals() {
        let currentSubtotal = parseFloat(originalSubtotal) || 0;
        deletedExistingIds.forEach(id => {
            const row = document.querySelector(`.existing-item[data-id="${id}"]`);
            if (!row) { return; }
            const quantity = parseFloat(row.children[1]?.textContent || 0) || 0;
            const unitPrice = parseFloat((row.children[2]?.textContent || '0').replace(/[^0-9.]/g, '')) || 0;
            currentSubtotal -= (quantity * unitPrice);
        });
        newItems.forEach(i => currentSubtotal += (parseFloat(i.line_total) || 0));
        const existingAddonTotal = Object.values(existingAddonUpdates).reduce((sum, entry) => sum + (parseFloat(entry.amount) || 0), 0);
        const newItemsAdditionTotal = newItems.reduce((sum, item) => sum + (parseFloat(item.addition_amount) || 0), 0);
        const newItemsTaxTotal = newItems.reduce((sum, item) => sum + (parseFloat(item.addition_tax) || 0), 0);
        const existingAddonTaxTotal = Object.values(existingAddonUpdates).reduce((sum, entry) => sum + (parseFloat(entry.tax) || 0), 0);
        const additionAmountTotal = newItemsAdditionTotal + existingAddonTotal;
        const additionTaxTotal = newItemsTaxTotal + existingAddonTaxTotal;
        const currentVat = parseFloat(originalVatAmount) + additionTaxTotal;
        const currentGrandTotal = parseFloat(originalGrandTotal) + additionAmountTotal + additionTaxTotal;
        document.getElementById('lblSubtotal').textContent = formatCurrency(currentSubtotal + existingAddonTotal);
        document.getElementById('lblVat').textContent = formatCurrency(currentVat);
        document.getElementById('lblGrandTotal').textContent = formatCurrency(currentGrandTotal);
        document.getElementById('lblAdditionalCharges').textContent = formatCurrency(additionAmountTotal);
        document.getElementById('lblAdditionalTax').textContent = formatCurrency(additionTaxTotal);
        document.getElementById('lblBalance').textContent = formatCurrency(additionAmountTotal + additionTaxTotal);
        renderHistory();
    }

    function renderAddons(addons, selectedValueIds = []) {
        addonContainer.innerHTML = '';
        addons.forEach(addon => {
            addonContainer.insertAdjacentHTML('beforeend', `<div class="mb-3"><label class="form-label d-block fw-bold">${addon.title}</label>${addon.values.map(val => `<div class="form-check mb-1"><input class="form-check-input addon-input" type="${addon.selection_type === 'multiple' ? 'checkbox' : 'radio'}" name="addon_${addon.id}" value="${val.id}" data-title="${val.title}" data-addon-title="${addon.title}" data-price="${val.price || 0}" ${selectedValueIds.includes(String(val.id)) ? 'checked' : ''}><label class="form-check-label">${val.title} ${val.price ? `(+${val.price})` : ''}</label></div>`).join('')}</div>`);
        });
    }

    document.getElementById('btnAddItem').addEventListener('click', function () {
        const productId = productSelect.val();
        if (!productId) { return; }
        const option = productSelect.find('option:selected');
        pendingProduct = { id: productId, title: option.attr('data-title') || '', price: parseFloat(option.attr('data-price')) || 0, addons: [] };
        const addons = JSON.parse(option.attr('data-addons') || '[]');
        if (addons.length > 0) { renderAddons(addons); addonProductTitle.textContent = pendingProduct.title; addonModal.show(); return; }
        const itemUnitPrice = pendingProduct.price;
        const item = { product_id: pendingProduct.id, title: pendingProduct.title, unit_price: itemUnitPrice, quantity: 1, line_total: itemUnitPrice, addition_amount: itemUnitPrice, addition_tax: calculateAdditionTax(itemUnitPrice), addons: [], uid: Date.now() };
        newItems.push(item);
        document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', `<tr id="item_${item.uid}" class="table-success"><td><div class="fw-bold text-success">${item.title}</div></td><td>1</td><td class="text-end">${formatCurrency(item.unit_price)}</td><td class="text-end">${formatCurrency(item.addition_amount)}</td><td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm js-remove-new" data-uid="${item.uid}"><i class="fa fa-times"></i></button></td></tr>`);
        pendingProduct = null;
        updateTotals();
    });

    function confirmAdditions() {
        const selectedAddonInputs = addonContainer.querySelectorAll('.addon-input:checked');
        const addedAddons = [];
        let addonTotal = 0;
        selectedAddonInputs.forEach(input => {
            const addonData = { value_id: String(input.value), title: input.getAttribute('data-title'), addon_title: input.getAttribute('data-addon-title'), price: parseFloat(input.getAttribute('data-price')) || 0 };
            pendingProduct.addons.push(addonData);
            addonTotal += addonData.price;
            addedAddons.push(addonData);
        });
        if (isEditingExisting && editingRowId) {
            existingAddonUpdates[editingRowId] = { title: pendingProduct.title, amount: addonTotal, addons: addedAddons, tax: calculateAdditionTax(addonTotal) };
            const row = document.querySelector(`.existing-item[data-id="${editingRowId}"] .new-addon-price`);
            if (row) { row.textContent = formatCurrency(addonTotal); }
            addonModal.hide(); pendingProduct = null; isEditingExisting = false; editingRowId = null; updateTotals(); return;
        }
        const itemUnitPrice = pendingProduct.price + addonTotal;
        const item = { product_id: pendingProduct.id, title: pendingProduct.title, unit_price: itemUnitPrice, quantity: 1, line_total: itemUnitPrice, addition_amount: itemUnitPrice, addition_tax: calculateAdditionTax(itemUnitPrice), addons: pendingProduct.addons, uid: Date.now() };
        newItems.push(item);
        document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', `<tr id="item_${item.uid}" class="table-success"><td><div class="fw-bold text-success">${item.title}</div></td><td>1</td><td class="text-end">${formatCurrency(item.unit_price)}</td><td class="text-end">${formatCurrency(item.addition_amount)}</td><td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm js-remove-new" data-uid="${item.uid}"><i class="fa fa-times"></i></button></td></tr>`);
        addonModal.hide(); pendingProduct = null; updateTotals();
    }

    document.getElementById('confirmAddons').addEventListener('click', confirmAdditions);

    $(document).on('click', '.js-edit-existing', function () {
        const row = $(this).closest('tr');
        const product = allProducts.find(p => p.id == row.data('product-id'));
        if (!product) { return; }
        pendingProduct = { id: product.id, title: product.title, price: parseFloat(product.base_price || product.price) || 0, addons: [] };
        isEditingExisting = true;
        editingRowId = row.data('id');
        renderAddons(product.addons || []);
        addonProductTitle.textContent = `${pendingProduct.title} (Editing)`;
        addonModal.show();
    });

    $(document).on('click', '.js-remove-new', function () { const uid = $(this).data('uid'); newItems = newItems.filter(i => i.uid != uid); document.getElementById(`item_${uid}`)?.remove(); updateTotals(); });
    $(document).on('click', '.js-remove-item', function () { if (!window.confirm(@json(__('confirm_remove_item')))) { return; } deletedExistingIds.push(String($(this).data('id'))); $(this).closest('tr').addClass('d-none'); updateTotals(); });

    document.getElementById('editOrderForm').addEventListener('submit', function () {
        document.getElementById('itemsJson').value = JSON.stringify({ new_items: newItems, deleted_ids: deletedExistingIds, existing_addons: Object.entries(existingAddonUpdates).map(([id, payload]) => ({ id, amount: payload.amount, addons: payload.addons, tax: payload.tax })) });
    });

    updateTotals();
});
</script>
@endsection
