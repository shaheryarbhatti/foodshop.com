<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('manage_shipping_fees') }}</title>

@extends('layouts.app')
@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card" style="margin-top:20px;">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('manage_shipping_fees') }}</h4>
                        <div class="d-flex gap-2">
                            <form action="{{ route('shipping.destroyAll') }}" method="POST" class="bulk-delete-form js-confirm-delete" data-text="{{ __('delete_selected_records_confirm') }}">
                                @csrf
                                @method('DELETE')
                                <div class="selected-ids-wrapper"></div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash me-2"></i>{{ __('delete_all') }}
                                </button>
                            </form>
                            <a href="{{ route('shipping.add') }}"
                                class="btn btn-primary"><i class="fa fa-plus me-2"></i>{{ __('add_new_shipping_fee') }}</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table id="shippingTable" class="display table table-hover table-bordered"
                                style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input select-all-checkbox"></th>
                                        <th>#</th>
                                        <th>{{ __('shipping_fee_title') }}</th>
                                        <th>{{ __('tax_title') }}</th>
                                        <th>{{ __('shipping_fee') }}</th>
                                        <th>{{ __('shipping_condition') }}</th>
                                        <th>{{ __('distance_value') }}</th>
                                    <th>{{ __('maximum_order_amount') }}</th>
                                    <th>{{ __('min_order_amount') }}</th>
                                    <th>{{ __('delivery_type') }}</th>
                                        <th>{{ __('status') }}</th>
                                        <th>{{ __('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
div.dataTables_wrapper div.dataTables_filter {
    position: absolute;
    right: 53px;
    top: 0px;
}

.dataTables_paginate .pagination {
    margin: 0 auto !important;
    justify-content: center;
    gap: 0.2rem;
    padding: 0.25rem 0;
}

.dataTables_paginate .page-item .page-link {
    border-radius: 8px !important;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    padding: 0;
    margin: 0 1px;
    border: 1px solid #e2e6ea;
    color: #5a6470;
    background-color: #fff;
    transition: all 0.15s ease;
    font-weight: 600;
    font-size: 0.85rem;
}

.dataTables_paginate .page-item .page-link:hover {
    background-color: var(--theme-default, #7367f0) !important;
    color: white !important;
    border-color: var(--theme-default, #7367f0) !important;
}

.dataTables_paginate .page-item.active .page-link {
    background-color: var(--theme-default, #7367f0) !important;
    border-color: var(--theme-default, #7367f0) !important;
    color: white !important;
    box-shadow: 0 3px 10px rgba(115, 103, 240, 0.25);
    font-weight: 700;
}

.dataTables_paginate .page-item.disabled .page-link {
    color: #b7c0c8 !important;
    background-color: #f6f7f9 !important;
    border-color: #e2e6ea !important;
    cursor: not-allowed;
}

.dataTables_paginate .page-item:first-child .page-link,
.dataTables_paginate .page-item:last-child .page-link {
    border-radius: 10px !important;
    width: auto;
    padding: 0 0.6rem;
    min-width: 44px;
    font-size: 0.8rem;
}

.dataTables_wrapper .dataTables_filter {
    margin-bottom: 2.5rem !important;
}

.dataTables_wrapper .dataTables_paginate {
    margin-top: 1rem !important;
}

@media (max-width: 576px) {
    .dataTables_paginate .page-link {
        width: 28px;
        height: 28px;
        line-height: 28px;
        font-size: 0.8rem;
    }
}
</style>
@endpush
@push('scripts')<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
$(function() {
    $('#shippingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('shipping.manage') }}',
        columns: [{
            data: 'bulk_select',
            name: 'bulk_select',
            orderable: false,
            searchable: false
        }, {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
        }, {
            data: 'title',
            name: 'shipping_fees.title'
        }, {
            data: 'tax_name',
            name: 'tax_name',
            orderable: false,
            searchable: false
        }, {
            data: 'fee_label',
            name: 'shipping_fees.fee',
            orderable: false,
            searchable: false
        }, {
            data: 'condition_type',
            name: 'shipping_fees.condition_type'
        }, {
            data: 'distance_value',
            name: 'shipping_fees.distance_value'
        }, {
            data: 'maximum_order_amount',
            name: 'shipping_fees.maximum_order_amount'
        }, {
            data: 'min_order_amount',
            name: 'shipping_fees.min_order_amount',
            orderable: false,
            searchable: false
        }, {
            data: 'delivery_type',
            name: 'shipping_fees.delivery_type'
        }, {
            data: 'status_badge',
            name: 'status_badge',
            orderable: false,
            searchable: false
        }, {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
        }],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip'
    });

    const syncSelectedIds = function(form) {
        const wrapper = form.find('.selected-ids-wrapper');
        wrapper.html('');
        $('#shippingTable .row-checkbox:checked').each(function() {
            wrapper.append('<input type="hidden" name="selected_ids[]" value="' + $(this).val() + '">');
        });
    };

    $(document).on('change', '#shippingTable .select-all-checkbox', function() {
        $('#shippingTable .row-checkbox').prop('checked', $(this).is(':checked'));
    });

    $(document).on('submit', '.bulk-delete-form', function(e) {
        syncSelectedIds($(this));
        if ($(this).find('input[name="selected_ids[]"]').length === 0) {
            e.preventDefault();
            alert('{{ __('select_records_to_delete') }}');
        }
    });
});
</script>@endpush
