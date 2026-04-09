@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>{{ __('edit_order') }} - {{ $order->order_number ?: ('#' . $order->id) }}</h3>
                </div>
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
                                        <th class="text-end">{{ __('line_total') }}</th>
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
                                        <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
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
                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-title="{{ $product->title }}" data-addons="{{ json_encode($product->addons) }}">
                                                            {{ $product->title }} ({{ number_format($product->price, 2) }})
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
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 pb-0">
                                <span class="h5 mb-0">{{ __('grand_total') }}</span>
                                <span class="h5 mb-0 text-primary" id="lblGrandTotal">{{ number_format($order->grand_total, 2) }}</span>
                            </li>
                        </ul>

                        <div class="alert alert-info border-3 border-start">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>{{ __('amount_paid') }}</span>
                                <span class="fw-bold">{{ number_format($order->amount_paid, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center h5 mb-0">
                                <span>{{ __('balance') }}</span>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = $('#productSelect').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        let newItems = [];
        let deletedExistingIds = [];
        const originalPaidAmount = {{ $order->amount_paid }};
        const originalSubtotal = {{ $order->subtotal }};
        const vatRate = @json(\App\Models\Tax::where('status', true)->orderBy('id')->first()?->amount ?? 0);
        const vatType = @json(\App\Models\Tax::where('status', true)->orderBy('id')->first()?->calculation_type ?? 'percentage');
        const shippingCosts = {{ $order->shipping_costs }}; // Keep shipping as is for now or recalculate if needed

        const btnAddItem = document.getElementById('btnAddItem');
        const addonModal = new bootstrap.Modal(document.getElementById('addonModal'));
        const addonContainer = document.getElementById('addonContainer');
        const addonProductTitle = document.getElementById('addonProductTitle');
        const confirmAddons = document.getElementById('confirmAddons');
        const itemsTableBody = document.querySelector('#itemsTable tbody');
        const editOrderForm = document.getElementById('editOrderForm');

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
            const currentAddons = row.data('current-addons') || [];

            // Extract selected value IDs for pre-selection
            let selectedValueIds = [];
            currentAddons.forEach(group => {
                if (group.values && Array.isArray(group.values)) {
                    group.values.forEach(v => selectedValueIds.push(String(v.id || v.value_id)));
                } else if (group.value_id) {
                    selectedValueIds.push(String(group.value_id));
                }
            });

            const product = allProducts.find(p => p.id == productId);
            if (!product) return;

            pendingProduct = {
                id: product.id,
                title: product.title,
                price: parseFloat(product.price) || 0,
                addons: []
            };

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
            const selectedAddonInputs = addonContainer.querySelectorAll('.addon-input:checked');
            selectedAddonInputs.forEach(input => {
                pendingProduct.addons.push({
                    value_id: input.value,
                    title: input.getAttribute('data-title'),
                    addon_title: input.getAttribute('data-addon-title'),
                    price: parseFloat(input.getAttribute('data-price'))
                });
            });

            if (isEditingExisting && editingRowId) {
                // Mark original for deletion
                if (!deletedExistingIds.includes(String(editingRowId))) {
                    deletedExistingIds.push(String(editingRowId));
                    const row = document.querySelector(`.existing-item[data-id="${editingRowId}"]`);
                    if (row) row.classList.add('d-none');
                }
            }

            addItemToTable(pendingProduct);
            addonModal.hide();
            pendingProduct = null;
            isEditingExisting = false;
            editingRowId = null;
        });

        function addItemToTable(product) {
            let itemUnitPrice = parseFloat(product.price) || 0;
            product.addons.forEach(a => itemUnitPrice += (parseFloat(a.price) || 0));

            const item = {
                product_id: product.id,
                title: product.title,
                unit_price: itemUnitPrice,
                quantity: 1,
                line_total: itemUnitPrice,
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
                    <td class="text-end">${item.line_total.toFixed(2)}</td>
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

            let currentVat = 0;
            const parsedVatRate = parseFloat(vatRate) || 0;
            if (vatType === 'percentage') {
                currentVat = currentSubtotal * (parsedVatRate / 100);
            } else {
                currentVat = parsedVatRate;
            }

            const currentGrandTotal = currentSubtotal + currentVat + (parseFloat(shippingCosts) || 0);
            const balance = currentGrandTotal - (parseFloat(originalPaidAmount) || 0);

            document.getElementById('lblSubtotal').textContent = currentSubtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('lblVat').textContent = currentVat.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('lblGrandTotal').textContent = currentGrandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('lblBalance').textContent = balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            if (balance > 0.01) {
                document.getElementById('lblBalance').classList.add('text-danger');
            } else {
                document.getElementById('lblBalance').classList.remove('text-danger');
            }
        }

        editOrderForm.addEventListener('submit', function(e) {
            const data = {
                new_items: newItems,
                deleted_ids: deletedExistingIds
            };
            document.getElementById('itemsJson').value = JSON.stringify(data);
        });

        // Initialize display
        updateTotals();
    });
</script>
@endpush
