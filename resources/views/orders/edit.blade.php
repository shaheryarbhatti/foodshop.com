@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
        <div class="col-6">
            <h3>{{ __('edit_order') }} - {{ $order->order_number ?: ('#' . $order->id) }}</h3>
        </div>
        @if($order->admin_updated)
            <div class="col-12">
                <div class="alert alert-warning">
                    {{ __('order_updated_by_admin_note') }}
                </div>
            </div>
        @endif
    </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>{{ __('ordered_items') }}</h5>
                    </div>
                    <div class="card-body">
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
                                    <tr class="existing-item"
                                        data-id="{{ $item->id }}"
                                        data-product-id="{{ $item->product_id }}"
                                        data-current-addons="{{ json_encode($item->addons) }}">
                                        <td>
                                            <div class="fw-bold">{{ $item->title }}</div>
                                            @if($item->addons)
                                                <div class="small text-muted">
                                                    @foreach($item->addons as $groupOrAddon)
                                                        @if(isset($groupOrAddon['values']) && is_array($groupOrAddon['values']))
                                                            {{-- Original structure: Group with nested values --}}
                                                            <span class="badge bg-light text-dark border me-1">
                                                                {{ $groupOrAddon['title'] }}: {{ collect($groupOrAddon['values'])->pluck('title')->join(', ') }}
                                                            </span>
                                                        @else
                                                            {{-- Flattened structure (from new additions) --}}
                                                            <span class="badge bg-light text-dark border me-1">
                                                                {{ $groupOrAddon['addon_title'] ?? 'Addon' }}: {{ $groupOrAddon['title'] }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end new-addon-price" data-item-id="{{ $item->id }}">
                                            {{ number_format($item->addition_amount ?? 0, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button type="button" class="btn btn-info btn-sm js-edit-existing" title="{{ __('edit') }}">
                                                    <i class="fa fa-edit text-white"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm js-remove-item" data-id="{{ $item->id }}" data-type="existing">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5">
                                            <div class="d-flex gap-2">
                                                <select id="productSelect" class="form-select select2">
                                                    <option value="">{{ __('select_products') }}</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->base_price }}" data-title="{{ $product->title }}" data-addons="{{ json_encode($product->addons) }}">
                                                            {{ $product->title }} ({{ number_format((float) $product->base_price, 2) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-primary" id="btnAddItem">
                                                    <i class="fa fa-plus me-1"></i> {{ __('add') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>{{ __('order_summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                                <span class="text-muted">{{ __('subtotal') }}</span>
                                <span class="fw-bold" id="lblSubtotal">{{ number_format($order->subtotal, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                                <span class="text-muted">{{ __('shipping_costs') }}</span>
                                <span class="fw-bold" id="lblShipping">{{ number_format($order->shipping_costs, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                                <span class="text-muted">{{ __('vat_amount') }}</span>
                                <span class="fw-bold" id="lblVat">{{ number_format($order->vat_amount, 2) }}</span>
                            </li>
                            @if((float) $order->discount_amount > 0)
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                                    <span class="text-muted">{{ __('coupon_discount') }}{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                                    <span class="fw-bold text-success" id="lblDiscount">-{{ number_format($order->discount_amount, 2) }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 pb-0">
                                <span class="h5 mb-0">{{ __('grand_total') }}</span>
                                <span class="h5 mb-0 text-primary" id="lblGrandTotal">{{ number_format($order->grand_total, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-white border-0 px-0 pt-0">
                                <span class="text-muted">{{ __('additional_payment_required') }}</span>
                                <span class="fw-bold" id="lblAdditionalCharges">0.00</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 pb-0">
                                <span class="text-muted">{{ __('additional_tax') }}</span>
                                <span class="fw-bold" id="lblAdditionalTax">0.00</span>
                            </li>
                        </ul>

                        <div class="alert alert-info border-3 border-start">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>{{ __('amount_paid') }}</span>
                                <span class="fw-bold">{{ number_format($order->amount_paid, 2) }}</span>
                            </div>
                        <div class="d-flex justify-content-between align-items-center h5 mb-0">
                            <span>{{ __('new_balance') }}</span>
                                <span class="fw-bold text-danger" id="lblBalance">0.00</span>
                            </div>
                        </div>

                        <form id="editOrderForm" action="{{ route('orders.update', $order) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="items_json" id="itemsJson">
                            <button type="submit" class="btn btn-primary w-100 py-3 shadow-sm">
                                <i class="fa fa-save me-2"></i> {{ __('update_order') }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header pb-0">
                        <h6>{{ __('change_history') }}</h6>
                    </div>
                    <div class="card-body py-2">
                        <div id="historyContainer" class="list-group list-group-flush">
                            <div class="text-muted small">{{ __('history_empty') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Addon Selection Modal --}}
<div class="modal fade" id="addonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                        <h5 class="modal-title" id="addonProductTitle">{{ __('addons') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addonContainer">
                {{-- Addons will be injected here --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirmAddons">{{ __('confirm') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .select2-container--bootstrap-5 { font-size: 1rem; }
    .existing-item { opacity: 0.8; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@php
    $historyEntries = [];
    foreach ($order->items as $item) {
        $additionAmount = (float) ($item->addition_amount ?? 0);
        $additionTax = (float) ($item->addition_tax ?? 0);
        if ($additionAmount <= 0 && $additionTax <= 0) {
            continue;
        }

        $historyEntries[] = [
            'item' => $item->title,
            'source_key' => $additionAmount > 0 && abs($additionAmount - (float) $item->line_total) < 0.01 ? 'newItem' : 'addonUpdate',
            'addons' => $item->addons ?? [],
            'amount' => $additionAmount,
            'tax' => $additionTax,
        ];
    }
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = $('#productSelect').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        let newItems = [];
        let deletedExistingIds = [];
        let existingAddonUpdates = {};
        const originalPaidAmount = parseFloat(@json($order->amount_paid ?? 0)) || 0;
        const originalSubtotal = parseFloat(@json($order->subtotal ?? 0)) || 0;
        const originalVatAmount = parseFloat(@json($order->vat_amount ?? 0)) || 0;
        const originalGrandTotal = parseFloat(@json($order->grand_total ?? 0)) || 0;
        const vatRate = parseFloat(@json(\App\Models\Tax::where('status', true)->orderBy('id')->first()?->amount ?? 0)) || 0;
        const vatType = @json(\App\Models\Tax::where('status', true)->orderBy('id')->first()?->calculation_type ?? 'percentage');
        const shippingCosts = parseFloat(@json($order->shipping_costs ?? 0)) || 0; // Keep shipping as is for now or recalculate if needed

        const btnAddItem = document.getElementById('btnAddItem');
        const addonModal = new bootstrap.Modal(document.getElementById('addonModal'));
        const addonContainer = document.getElementById('addonContainer');
        const addonProductTitle = document.getElementById('addonProductTitle');
        const confirmAddons = document.getElementById('confirmAddons');
        const itemsTableBody = document.querySelector('#itemsTable tbody');
        const editOrderForm = document.getElementById('editOrderForm');
        const lblAdditionalCharges = document.getElementById('lblAdditionalCharges');
        const lblAdditionalTax = document.getElementById('lblAdditionalTax');
        const historyContainer = document.getElementById('historyContainer');
        const preexistingHistoryEntries = @json($historyEntries);

        let pendingProduct = null;
        let isEditingExisting = false;
        let editingRowId = null;

        // Step 1: Cache products for quick lookup
        const allProducts = @json($products);

        btnAddItem.addEventListener('click', function() {
            const productId = productSelect.val();
            if (!productId) return;

            const option = productSelect.find('option:selected');
            const addons = JSON.parse(option.attr('data-addons') || '[]');

            pendingProduct = {
                id: productId,
                title: option.attr('data-title') || '',
                price: parseFloat(option.attr('data-price')) || 0,
                addons: []
            };

            if (addons.length > 0) {
                isEditingExisting = false;
                editingRowId = null;
                renderAddons(addons);
                addonProductTitle.textContent = pendingProduct.title;
                addonModal.show();
            } else {
                addItemToTable(pendingProduct);
                pendingProduct = null;
            }
        });

        // Handler for editing existing items
        $(document).on('click', '.js-edit-existing', function() {
            const row = $(this).closest('tr');
            const productId = row.data('product-id');
            const rowId = row.data('id');
            const currentAddonsRaw = row.attr('data-current-addons') || row.data('current-addons') || [];
            let currentAddons = [];

            if (Array.isArray(currentAddonsRaw)) {
                currentAddons = currentAddonsRaw;
            } else if (typeof currentAddonsRaw === 'string' && currentAddonsRaw.trim() !== '') {
                try {
                    const parsedAddons = JSON.parse(currentAddonsRaw);
                    currentAddons = Array.isArray(parsedAddons) ? parsedAddons : [];
                } catch (error) {
                    currentAddons = [];
                }
            }

            // Extract selected value IDs for pre-selection
            let selectedValueIds = [];
            currentAddons.forEach(group => {
                if (group.values && Array.isArray(group.values)) {
                    group.values.forEach(v => selectedValueIds.push(String(v.id || v.value_id)));
                } else if (group.value_id) {
                    selectedValueIds.push(String(group.value_id));
                }
            });
            const existingAddonValueIds = [...new Set(selectedValueIds)];

            const product = allProducts.find(p => p.id == productId);
            if (!product) return;

            pendingProduct = {
                id: product.id,
                title: product.title,
                price: parseFloat(product.base_price || product.price) || 0,
                addons: []
            };
            pendingProduct.currentAddons = currentAddons;
            pendingProduct.existingAddonValueIds = existingAddonValueIds;

            isEditingExisting = true;
            editingRowId = rowId;

            renderAddons(product.addons, selectedValueIds);
            addonProductTitle.textContent = `${pendingProduct.title} (${@json(__('editing'))})`;
            addonModal.show();
        });

        function renderAddons(addons, selectedValueIds = []) {
            addonContainer.innerHTML = '';
            addons.forEach(addon => {
                const html = `
                    <div class="mb-3">
                        <label class="form-label d-block fw-bold">${addon.title}</label>
                        <div class="btn-group-toggle" data-toggle="buttons">
                            ${addon.values.map(val => {
                                const isChecked = selectedValueIds.includes(String(val.id));
                                return `
                                    <div class="form-check mb-1">
                                        <input class="form-check-input addon-input"
                                               type="${addon.selection_type === 'multiple' ? 'checkbox' : 'radio'}"
                                               name="addon_${addon.id}"
                                               id="val_${val.id}"
                                               value="${val.id}"
                                               data-title="${val.title}"
                                               data-addon-title="${addon.title}"
                                               data-price="${val.price || 0}"
                                               ${isChecked ? 'checked' : ''}>
                                        <label class="form-check-label" for="val_${val.id}">
                                            ${val.title} ${val.price ? `(+${val.price})` : ''}
                                        </label>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
                addonContainer.insertAdjacentHTML('beforeend', html);
            });
        }

        confirmAddons.addEventListener('click', function() {
            if (!pendingProduct) {
                return;
            }

            const selectedAddonInputs = addonContainer.querySelectorAll('.addon-input:checked');
            const existingIds = new Set(pendingProduct.existingAddonValueIds || []);
            const addedAddons = [];
            let addonTotal = 0;

            selectedAddonInputs.forEach(input => {
                const valueId = String(input.value);
                const price = parseFloat(input.getAttribute('data-price')) || 0;
                const addonData = {
                    value_id: valueId,
                    title: input.getAttribute('data-title'),
                    addon_title: input.getAttribute('data-addon-title'),
                    price,
                    is_existing: existingIds.has(valueId)
                };
                pendingProduct.addons.push(addonData);

                if (!addonData.is_existing) {
                    addonTotal += price;
                    addedAddons.push(addonData);
                }
            });

            if (isEditingExisting && editingRowId) {
                if (addonTotal > 0) {
                    const addonTax = calculateAdditionTax(addonTotal);
                    existingAddonUpdates[editingRowId] = {
                        title: pendingProduct.title,
                        amount: addonTotal,
                        addons: addedAddons,
                        tax: addonTax,
                        source_key: 'addonUpdate'
                    };
                } else {
                    delete existingAddonUpdates[editingRowId];
                }

                const row = document.querySelector(`.existing-item[data-id="${editingRowId}"]`);
                if (row) {
                    const priceCell = row.querySelector(`.new-addon-price[data-item-id="${editingRowId}"]`);
                    if (priceCell) {
                        priceCell.textContent = addonTotal.toFixed(2);
                    }
                    const mergedAddons = [...(pendingProduct.currentAddons || []), ...addedAddons];
                    row.dataset.currentAddons = JSON.stringify(mergedAddons);
                    $(row).data('current-addons', mergedAddons);
                }

                addonModal.hide();
                pendingProduct = null;
                isEditingExisting = false;
                editingRowId = null;
                updateTotals();
                return;
            }

            addItemToTable(pendingProduct);
            addonModal.hide();
            pendingProduct = null;
        });

        const historyTexts = {
            newItem: @json(__('history_new_item')),
            addonUpdate: @json(__('history_addon_update')),
            empty: @json(__('history_empty')),
            productTax: @json(__('history_product_tax'))
        };

        function calculateAdditionTax(amount) {
            const numericAmount = parseFloat(amount) || 0;
            if (!numericAmount) {
                return 0;
            }

            if (vatType === 'percentage') {
                return numericAmount * (vatRate / 100);
            }

            return vatRate;
        }

        function formatCurrency(value) {
            return (Number(value) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function formatAddons(addons) {
            if (!addons || !addons.length) return '-';
            return addons.map(addon => `${addon.addon_title || addon.title}: ${addon.title}`).join(' • ');
        }

        function renderHistory() {
            if (!historyContainer) return;
            const runtimeEntries = [];

            newItems.forEach(item => {
                runtimeEntries.push({
                    item: item.title,
                    amount: item.addition_amount,
                    tax: item.addition_tax
                });
            });

            Object.values(existingAddonUpdates).forEach(entry => {
                if (!entry.amount && !entry.tax) return;
                runtimeEntries.push({
                    item: entry.title || historyTexts.addonUpdate,
                    amount: entry.amount,
                    tax: entry.tax
                });
            });

            const allEntries = [
                ...preexistingHistoryEntries,
                ...runtimeEntries
            ];

            if (!allEntries.length) {
                historyContainer.innerHTML = `<div class="text-muted small">${historyTexts.empty}</div>`;
                return;
            }

            historyContainer.innerHTML = allEntries.map(entry => `
                <div class="list-group-item bg-white shadow-sm rounded-3 border-0 px-3 py-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">${entry.item}</strong>
                        <div class="text-success fw-bold">${formatCurrency(entry.amount)}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small text-muted">
                        <span>${historyTexts.productTax || 'Tax'}</span>
                        <span class="text-info fw-semibold">${formatCurrency(entry.tax)}</span>
                    </div>
                </div>
            `).join('');
        }

        function addItemToTable(product) {
            const addonTotal = product.addons.reduce((sum, addon) => sum + (parseFloat(addon.price) || 0), 0);
            const basePrice = parseFloat(product.price || product.base_price || product.unit_price) || 0;
            const itemUnitPrice = basePrice + addonTotal;
            const additionAmount = itemUnitPrice;
            const additionTax = calculateAdditionTax(additionAmount);

            const item = {
                product_id: product.id,
                title: product.title,
                unit_price: itemUnitPrice,
                quantity: 1,
                line_total: itemUnitPrice,
                addition_amount: additionAmount,
                addition_tax: additionTax,
                addons: product.addons,
                uid: Date.now()
            };

            newItems.push(item);

            const row = `
                <tr class="table-success" id="item_${item.uid}">
                    <td>
                        <div class="fw-bold text-success">${item.title} <small class="badge bg-success ms-1">New</small></div>
                        ${item.addons.length ? `
                            <div class="small text-muted">
                                ${item.addons.map(a => `<span class="badge bg-light text-dark border me-1">${a.addon_title}: ${a.title}</span>`).join('')}
                            </div>
                        ` : ''}
                    </td>
                    <td>1</td>
                    <td class="text-end">${item.unit_price.toFixed(2)}</td>
                                            <td class="text-end">${item.addition_amount.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm js-remove-new" data-uid="${item.uid}">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            itemsTableBody.insertAdjacentHTML('beforeend', row);
            updateTotals();
        }

        $(document).on('click', '.js-remove-new', function() {
            const uid = $(this).data('uid');
            newItems = newItems.filter(i => i.uid != uid);
            $(`#item_${uid}`).remove();
            updateTotals();
        });

        $(document).on('click', '.js-remove-item', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: @json(__('confirm_delete_title')),
                text: @json(__('confirm_remove_item')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: @json(__('confirm_delete_confirm')),
                cancelButtonText: @json(__('confirm_delete_cancel')),
                padding: '2em'
            }).then((result) => {
                if (result.isConfirmed) {
                    deletedExistingIds.push(String(id));
                    delete existingAddonUpdates[id];
                    const priceCell = document.querySelector(`.new-addon-price[data-item-id="${id}"]`);
                    if (priceCell) {
                        priceCell.textContent = '0.00';
                    }
                    btn.closest('tr').addClass('d-none');
                    updateTotals();
                }
            });
        });

        function updateTotals() {
            let currentSubtotal = parseFloat(originalSubtotal) || 0;

            // Subtract deleted items from original subtotal
            deletedExistingIds.forEach(id => {
                const row = document.querySelector(`.existing-item[data-id="${id}"]`);
                if (row) {
                    // Extract only numbers and decimal point
                    const lineTotalText = row.children[3].textContent.replace(/[^0-9.]/g, '');
                    const lineTotal = parseFloat(lineTotalText) || 0;
                    currentSubtotal -= lineTotal;
                }
            });

            // Add new items
            newItems.forEach(i => currentSubtotal += (parseFloat(i.line_total) || 0));
            const existingAddonTotal = Object.values(existingAddonUpdates).reduce((sum, entry) => sum + (parseFloat(entry.amount) || 0), 0);
            currentSubtotal += existingAddonTotal;
            const newItemsAdditionTotal = newItems.reduce((sum, item) => sum + (parseFloat(item.addition_amount) || 0), 0);
            const newItemsTaxTotal = newItems.reduce((sum, item) => sum + (parseFloat(item.addition_tax) || 0), 0);
            const existingAddonTaxTotal = Object.values(existingAddonUpdates).reduce((sum, entry) => sum + (parseFloat(entry.tax) || 0), 0);
            const additionAmountTotal = newItemsAdditionTotal + existingAddonTotal;
            const additionTaxTotal = newItemsTaxTotal + existingAddonTaxTotal;
            const currentVat = parseFloat(originalVatAmount) + additionTaxTotal;
            const currentGrandTotal = parseFloat(originalGrandTotal) + additionAmountTotal + additionTaxTotal;
            const balanceDue = Math.round(Math.max(additionAmountTotal + additionTaxTotal, 0) * 100) / 100;

            document.getElementById('lblSubtotal').textContent = currentSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('lblVat').textContent = currentVat.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('lblGrandTotal').textContent = currentGrandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('lblBalance').textContent = balanceDue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            if (lblAdditionalCharges) {
                lblAdditionalCharges.textContent = additionAmountTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            if (lblAdditionalTax) {
                lblAdditionalTax.textContent = additionTaxTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            if (balanceDue > 0.01) {
                document.getElementById('lblBalance').classList.add('text-danger');
            } else {
                document.getElementById('lblBalance').classList.remove('text-danger');
            }
            renderHistory();
        }

        editOrderForm.addEventListener('submit', function(e) {
            const existingPayload = Object.entries(existingAddonUpdates).map(([id, payload]) => ({
                id,
                amount: payload.amount,
                addons: payload.addons,
                tax: payload.tax
            }));
            const data = {
                new_items: newItems,
                deleted_ids: deletedExistingIds,
                existing_addons: existingPayload
            };
            document.getElementById('itemsJson').value = JSON.stringify(data);
        });

        // Initialize display
        updateTotals();
    });
</script>
@endpush
