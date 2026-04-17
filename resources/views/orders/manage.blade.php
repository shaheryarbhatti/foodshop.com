<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('manage_orders') }}</title>

@extends('layouts.app')
@section('content')
@php
    $mapProvider = \App\Models\Setting::get('map_provider', 'leaflet');
    $googleMapsApiKey = \App\Models\Setting::get('google_maps_api_key', '');
    $canUseGoogleMaps = $mapProvider === 'google' && filled($googleMapsApiKey);
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="row g-4" style="margin-top: 80px;">
            @foreach($statusCards as $card)
                <div class="col-12 col-sm-6 col-xl-3">
                    <button type="button" class="order-status-card {{ $card['theme'] }} w-100 border-0 text-start js-order-filter-card {{ $card['key'] === 'all' ? 'is-active' : '' }}" data-status="{{ $card['key'] }}">
                        <span class="order-status-card__icon"><i class="fa {{ $card['icon'] }}"></i></span>
                        <span class="order-status-card__content">
                            <span class="order-status-card__title">{{ $card['title'] }}</span>
                            <span class="order-status-card__count">{{ number_format((int) $card['count']) }}</span>
                        </span>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card order-manage-card ">
                    <div class="card-header pb-0 d-flex flex-wrap gap-3 justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">{{ __('manage_orders') }}</h4>
                            <p class="text-muted mb-0">{{ __('order_management_summary') }}</p>
                        </div>
                        <span class="badge rounded-pill text-bg-light border" id="activeOrderFilterLabel">{{ __('all_orders') }}</span>
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
                            <table id="ordersTable" class="display table table-hover table-bordered align-middle" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('order_reference') }}</th>
                                        <th>{{ __('customer') }}</th>
                                        <th>{{ __('branch') }}</th>
                                        <th>{{ __('items') }}</th>
                                        <th>{{ __('order_type') }}</th>
                                        <th>{{ __('payment') }}</th>
                                        <th>{{ __('status') }}</th>
                                        <th>{{ __('grand_total') }}</th>
                                        <th>{{ __('extra_paid_amount') }}</th>
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

<div class="modal fade" id="orderRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content order-route-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">{{ __('delivery_route') }}</div>
                    <h5 class="modal-title mb-1" id="orderRouteTitle">{{ __('order_route_preview') }}</h5>
                    <small class="text-muted" id="orderRouteMeta">{{ __('route_not_available') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="route-info-card">
                            <div class="route-party-card">
                                <div class="route-party-card__icon bg-primary-subtle text-primary"><i class="fa fa-store"></i></div>
                                <div>
                                    <div class="route-party-card__label">{{ __('branch') }}</div>
                                    <div class="route-party-card__title" id="routeBranchName">-</div>
                                    <div class="route-party-card__text" id="routeBranchAddress">-</div>
                                </div>
                            </div>

                            <div class="route-connector">
                                <span></span>
                                <small id="routeDistanceText">{{ __('distance_not_available') }}</small>
                                <span></span>
                            </div>

                            <div class="route-party-card">
                                <div class="route-party-card__icon bg-success-subtle text-success"><i class="fa fa-location-dot"></i></div>
                                <div>
                                    <div class="route-party-card__label">{{ __('customer') }}</div>
                                    <div class="route-party-card__title" id="routeCustomerName">-</div>
                                    <div class="route-party-card__text" id="routeCustomerAddress">-</div>
                                </div>
                            </div>

                            <div class="route-coordinates">
                                <div>
                                    <span>{{ __('branch_coordinates') }}</span>
                                    <strong id="routeBranchCoordinates">-</strong>
                                </div>
                                <div>
                                    <span>{{ __('customer_coordinates') }}</span>
                                    <strong id="routeCustomerCoordinates">-</strong>
                                </div>
                            </div>

                            <a href="#" class="btn btn-primary w-100 mt-4 d-none" id="routeGoogleMapsButton" target="_blank" rel="noopener">
                                <i class="fa fa-location-arrow me-2"></i>{{ __('open_in_google_maps') }}
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="route-map-shell">
                            <div class="route-map-shell__head">
                                <div>
                                    <h6 class="mb-1">{{ __('map_preview') }}</h6>
                                    <small class="text-muted">{{ __('route_map_help_text') }}</small>
                                </div>
                            </div>
                            <div id="orderRouteMapCanvas"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content order-invoice-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">{{ __('invoice') }}</div>
                    <h5 class="modal-title mb-1" id="orderInvoiceTitle">{{ __('invoice') }}</h5>
                    <small class="text-muted">{{ __('manage_orders') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="order-invoice-frame-wrap">
                    <iframe id="orderInvoiceFrame" class="order-invoice-frame" src="about:blank" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content order-status-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">{{ __('status') }}</div>
                    <h5 class="modal-title mb-1" id="orderStatusModalTitle">{{ __('status') }}</h5>
                    <small class="text-muted">{{ __('manage_orders') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="orderStatusForm">
                    <input type="hidden" id="orderStatusUpdateUrl">
                    <div class="mb-3">
                        <label for="orderStatusSelect" class="form-label fw-semibold">{{ __('status') }}</label>
                        <select id="orderStatusSelect" class="form-select">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-warning small mb-3 d-none" id="orderStatusPaymentNotice">{{ __('offline_payment_delivery_notice') }}</div>
                    <div class="form-check mb-3 d-none" id="orderStatusPaymentConfirmWrap">
                        <input class="form-check-input" type="checkbox" id="orderStatusPaymentConfirm">
                        <label class="form-check-label fw-semibold" for="orderStatusPaymentConfirm">{{ __('confirm_payment_received_checkbox') }}</label>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="orderStatusSaveButton">{{ __('save_settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content order-status-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">Driver Assignment</div>
                    <h5 class="modal-title mb-1" id="assignDriverModalTitle">Assign Driver</h5>
                    <small class="text-muted">Drivers are filtered by the branch attached to this order.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="assignDriverForm">
                    <input type="hidden" id="assignDriverOrderId">
                    <div class="mb-3">
                        <label for="assignDriverSelect" class="form-label fw-semibold">Driver</label>
                        <select id="assignDriverSelect" class="form-select"></select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="assignDriverSaveButton">Assign Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content order-status-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">{{ __('change_branch') }}</div>
                    <h5 class="modal-title mb-1" id="changeBranchModalTitle">{{ __('change_branch') }}</h5>
                    <small class="text-muted">{{ __('change_branch_modal_hint') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="changeBranchForm">
                    <input type="hidden" id="changeBranchOrderId">
                    <div class="alert alert-info small mb-3">{{ __('branch_change_notice') }}</div>
                    <div class="mb-3">
                        <label for="changeBranchSelect" class="form-label fw-semibold">{{ __('select_branch') }}</label>
                        <select id="changeBranchSelect" class="form-select"></select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="changeBranchSaveButton">{{ __('change_branch_button') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
@unless($canUseGoogleMaps)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endunless
<style>
div.dataTables_wrapper div.dataTables_filter { position: absolute; right: 53px; top: 0; }
.order-manage-card { margin-top: 18px; border: 0; border-radius: 26px; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08); }
.order-status-card { position: relative; display: flex; align-items: center; gap: 18px; min-height: 126px; padding: 24px 22px; border-radius: 24px; box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08); transition: transform .2s ease, box-shadow .2s ease, outline-color .2s ease; outline: 2px solid transparent; }
.order-status-card:hover,.order-status-card.is-active { transform: translateY(-3px); box-shadow: 0 24px 44px rgba(15, 23, 42, 0.12); outline-color: rgba(15, 23, 42, 0.14); }
.order-status-card__icon { display: inline-flex; align-items: center; justify-content: center; width: 74px; height: 74px; border-radius: 50%; background: rgba(255, 255, 255, 0.88); color: #1f2937; font-size: 1.9rem; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7); }
.order-status-card__content { display: flex; flex-direction: column; gap: 8px; }
.order-status-card__title { color: #334155; font-size: 1.1rem; font-weight: 700; }
.order-status-card__count { color: #0f172a; font-size: 2rem; font-weight: 800; line-height: 1; letter-spacing: -0.03em; }
.order-card-total { background: linear-gradient(135deg, #b8cff2, #9fbee8); }
.order-card-pending { background: linear-gradient(135deg, #fff0a8, #fee786); }
.order-card-processing { background: linear-gradient(135deg, #c8eceb, #b0e0df); }
.order-card-shipped { background: linear-gradient(135deg, #ffd7b4, #f8c89f); }
.order-card-delivered { background: linear-gradient(135deg, #f4d8ea, #edcfe1); }
.order-card-cancelled { background: linear-gradient(135deg, #ffd89d, #f8cb80); }
.order-card-returned { background: linear-gradient(135deg, #c9f0a8, #b7e792); }
.order-card-failed { background: linear-gradient(135deg, #c8e9fb, #b5ddf3); }
.dataTables_paginate .pagination { margin: 0 auto !important; justify-content: center; gap: 0.2rem; padding: 0.25rem 0; }
.dataTables_paginate .page-item .page-link { border-radius: 8px !important; width: 32px; height: 32px; line-height: 32px; text-align: center; padding: 0; margin: 0 1px; border: 1px solid #e2e6ea; color: #5a6470; background-color: #fff; transition: all .15s ease; font-weight: 600; font-size: .85rem; }
.dataTables_paginate .page-item .page-link:hover { background-color: var(--theme-default, #7367f0) !important; color: white !important; border-color: var(--theme-default, #7367f0) !important; }
.dataTables_paginate .page-item.active .page-link { background-color: var(--theme-default, #7367f0) !important; border-color: var(--theme-default, #7367f0) !important; color: white !important; box-shadow: 0 3px 10px rgba(115, 103, 240, 0.25); font-weight: 700; }
.dataTables_paginate .page-item.disabled .page-link { color: #b7c0c8 !important; background-color: #f6f7f9 !important; border-color: #e2e6ea !important; cursor: not-allowed; }
.dataTables_paginate .page-item:first-child .page-link,.dataTables_paginate .page-item:last-child .page-link { border-radius: 10px !important; width: auto; padding: 0 .6rem; min-width: 44px; font-size: .8rem; }
.dataTables_wrapper .dataTables_filter { margin-bottom: 2.5rem !important; }
.dataTables_wrapper .dataTables_paginate { margin-top: 1rem !important; }
.order-route-modal .modal-content { border: 0; border-radius: 28px; box-shadow: 0 30px 80px rgba(15, 23, 42, 0.2); overflow: hidden; }
.order-invoice-modal { border: 0; border-radius: 28px; box-shadow: 0 30px 80px rgba(15, 23, 42, 0.2); overflow: hidden; }
.order-status-modal { border: 0; border-radius: 22px; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18); overflow: hidden; }
.order-route-modal__eyebrow { display: inline-flex; padding: 6px 12px; margin-bottom: 8px; border-radius: 999px; background: rgba(59, 130, 246, 0.1); color: #2563eb; font-size: .78rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.order-invoice-frame-wrap { border-radius: 24px; overflow: hidden; border: 1px solid rgba(15, 23, 42, 0.08); background: #f8fafc; }
.order-invoice-frame { display: block; width: 100%; min-height: 82vh; border: 0; background: #fff; }
.route-info-card,.route-map-shell { height: 100%; border-radius: 24px; background: linear-gradient(180deg, #ffffff, #f8fafc); border: 1px solid rgba(15, 23, 42, 0.08); }
.route-info-card { padding: 24px; }
.route-party-card { display: flex; gap: 14px; align-items: flex-start; padding: 16px; border-radius: 18px; background: #fff; border: 1px solid rgba(15, 23, 42, 0.07); box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05); }
.route-party-card + .route-party-card { margin-top: 18px; }
.route-party-card__icon { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 14px; font-size: 1.15rem; flex-shrink: 0; }
.route-party-card__label { color: #64748b; font-size: .76rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px; }
.route-party-card__title { color: #0f172a; font-size: 1rem; font-weight: 800; margin-bottom: 4px; }
.route-party-card__text { color: #475569; line-height: 1.55; }
.route-connector { display: flex; align-items: center; gap: 12px; margin: 18px 0; color: #64748b; font-size: .85rem; font-weight: 700; }
.route-connector span { flex: 1; height: 1px; background: linear-gradient(90deg, rgba(148, 163, 184, 0.1), rgba(148, 163, 184, 0.7), rgba(148, 163, 184, 0.1)); }
.route-coordinates { display: grid; gap: 12px; margin-top: 20px; }
.route-coordinates div { padding: 14px 16px; border-radius: 16px; background: rgba(15, 23, 42, 0.03); }
.route-coordinates span { display: block; color: #64748b; font-size: .76rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
.route-coordinates strong { color: #0f172a; font-size: .95rem; }
.route-map-shell__head { padding: 22px 24px 0; }
#orderRouteMapCanvas { height: 480px; margin: 20px; border-radius: 20px; overflow: hidden; background: linear-gradient(180deg, #eff6ff, #f8fafc); }
.leaflet-popup-content-wrapper { border-radius: 14px; }
.select2-container { width: 100% !important; }
.select2-container .select2-selection--single { min-height: 44px; border-radius: 12px; border-color: rgba(15, 23, 42, 0.12); padding: 7px 12px; }
.select2-container .select2-selection--single .select2-selection__rendered { line-height: 28px; padding-left: 0; }
.select2-container .select2-selection--single .select2-selection__arrow { height: 42px; }
@media (max-width: 991.98px) { #orderRouteMapCanvas { height: 360px; } }
@media (max-width: 576px) {
div.dataTables_wrapper div.dataTables_filter { position: static; margin-top: 12px; }
.order-status-card { min-height: 112px; padding: 20px 18px; }
.order-status-card__icon { width: 62px; height: 62px; font-size: 1.45rem; }
.order-status-card__count { font-size: 1.7rem; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
@unless($canUseGoogleMaps)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endunless
<script>
window.initOrdersManagePage = function () {
    if (@json($canUseGoogleMaps) && !(window.google && window.google.maps)) { return; }
    if (window.__ordersManagePageInitialized) { return; }
    window.__ordersManagePageInitialized = true;

    const statusLabels = @json(['all' => __('all_orders')] + $statusOptions);
    const table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: '{{ route('orders.manage') }}',
            data: function (d) { d.status_filter = $('.js-order-filter-card.is-active').data('status') || 'all'; }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_reference', name: 'order_number' },
            { data: 'customer_summary', name: 'first_name' },
            { data: 'branch_summary', name: 'organization.name', orderable: false, searchable: false },
            { data: 'items_summary', name: 'items.title', orderable: false, searchable: false },
            { data: 'order_type_badge', name: 'order_type', orderable: false, searchable: false },
            { data: 'payment_summary', name: 'payment_method', orderable: false, searchable: false },
            { data: 'status_selector', name: 'order_status', orderable: false, searchable: false },
            { data: 'grand_total_display', name: 'grand_total', searchable: false },
            { data: 'extra_paid_display', name: 'extra_amount_paid', searchable: false, orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [],
        language: { processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>' },
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip'
    });

    $(document).on('click', '.js-order-filter-card', function () {
        $('.js-order-filter-card').removeClass('is-active');
        $(this).addClass('is-active');
        const status = $(this).data('status');
        $('#activeOrderFilterLabel').text(statusLabels[status] || statusLabels.all);
        table.ajax.reload();
    });

    const updateOrderStatus = function (updateUrl, nextValue, extraData, onSuccess, onError) {
        $.ajax({
            url: updateUrl,
            type: 'PATCH',
            data: Object.assign({ _token: @json(csrf_token()), order_status: nextValue }, extraData || {}),
            success: onSuccess,
            error: onError
        });
    };

    const routeModalElement = document.getElementById('orderRouteModal');
    const routeModal = new bootstrap.Modal(routeModalElement);
    const invoiceModalElement = document.getElementById('orderInvoiceModal');
    const invoiceModal = new bootstrap.Modal(invoiceModalElement);
    const statusModalElement = document.getElementById('orderStatusModal');
    const statusModal = new bootstrap.Modal(statusModalElement);
    const assignDriverModalElement = document.getElementById('assignDriverModal');
    const assignDriverModal = new bootstrap.Modal(assignDriverModalElement);
    const changeBranchModalElement = document.getElementById('changeBranchModal');
    const changeBranchModal = new bootstrap.Modal(changeBranchModalElement);
    const invoiceFrame = document.getElementById('orderInvoiceFrame');
    const invoiceTitle = document.getElementById('orderInvoiceTitle');
    const orderStatusModalTitle = document.getElementById('orderStatusModalTitle');
    const orderStatusForm = document.getElementById('orderStatusForm');
    const orderStatusSelect = document.getElementById('orderStatusSelect');
    const orderStatusUpdateUrl = document.getElementById('orderStatusUpdateUrl');
    const orderStatusSaveButton = document.getElementById('orderStatusSaveButton');
    const orderStatusPaymentNotice = document.getElementById('orderStatusPaymentNotice');
    const orderStatusPaymentConfirmWrap = document.getElementById('orderStatusPaymentConfirmWrap');
    const orderStatusPaymentConfirm = document.getElementById('orderStatusPaymentConfirm');
    const assignDriverModalTitle = document.getElementById('assignDriverModalTitle');
    const assignDriverForm = document.getElementById('assignDriverForm');
    const assignDriverOrderId = document.getElementById('assignDriverOrderId');
    const assignDriverSelect = document.getElementById('assignDriverSelect');
    const assignDriverSaveButton = document.getElementById('assignDriverSaveButton');
    const changeBranchModalTitle = document.getElementById('changeBranchModalTitle');
    const changeBranchForm = document.getElementById('changeBranchForm');
    const changeBranchOrderId = document.getElementById('changeBranchOrderId');
    const changeBranchSelect = document.getElementById('changeBranchSelect');
    const changeBranchSaveButton = document.getElementById('changeBranchSaveButton');
    const routeGoogleMapsButton = document.getElementById('routeGoogleMapsButton');
    let leafletMap;
    let leafletMarkers = [];
    let leafletPolyline;
    let googleMap;
    let googleDirectionsService;
    let googleDirectionsRenderer;
    let googleBranchMarker;
    let googleCustomerMarker;
    let googleFallbackPolyline;
    let routeRequestToken = 0;
    let orderStatusPaymentMethod = '';
    let orderStatusPaymentStatus = '';

    const syncOrderStatusPaymentConfirmation = function () {
        const requiresConfirmation = ['cash_on_delivery', 'bank_account'].includes(orderStatusPaymentMethod)
            && !['paid', 'success'].includes(orderStatusPaymentStatus)
            && orderStatusSelect?.value === @json(\App\Models\Order::STATUS_DELIVERED);

        orderStatusPaymentNotice?.classList.toggle('d-none', !requiresConfirmation);
        orderStatusPaymentConfirmWrap?.classList.toggle('d-none', !requiresConfirmation);

        if (orderStatusPaymentConfirm) {
            orderStatusPaymentConfirm.checked = false;
        }
    };

    const initBranchSelect = function () {
        if (!(window.jQuery && jQuery.fn.select2 && changeBranchSelect)) { return; }

        const $select = $('#changeBranchSelect');
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            width: '100%',
            dropdownParent: $('#changeBranchModal'),
            placeholder: @json(__('select_branch')),
            allowClear: true
        });
    };

    const resetLeafletRoute = function () {
        if (!leafletMap) { return; }
        leafletMarkers.forEach(marker => leafletMap.removeLayer(marker));
        leafletMarkers = [];
        if (leafletPolyline) { leafletMap.removeLayer(leafletPolyline); leafletPolyline = null; }
    };

    const drawLeafletRoute = function (branch, customer) {
        if (!leafletMap) {
            leafletMap = L.map('orderRouteMapCanvas', { zoomControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(leafletMap);
        }
        resetLeafletRoute();
        const branchMarker = L.marker([branch.lat, branch.lng]).addTo(leafletMap).bindPopup(`<strong>${branch.name}</strong><br><small>${branch.address}</small>`);
        const customerMarker = L.marker([customer.lat, customer.lng]).addTo(leafletMap).bindPopup(`<strong>${customer.name}</strong><br><small>${customer.address}</small>`);
        leafletMarkers.push(branchMarker, customerMarker);
    };

    const fallbackLeafletStraightLine = function (branch, customer) {
        if (!leafletMap) { return; }
        if (leafletPolyline) {
            leafletMap.removeLayer(leafletPolyline);
        }
        leafletPolyline = L.polyline([[branch.lat, branch.lng], [customer.lat, customer.lng]], { color: '#2563eb', weight: 4, opacity: 0.85, dashArray: '8 10' }).addTo(leafletMap);
        leafletMap.fitBounds(leafletPolyline.getBounds(), { padding: [40, 40] });
        setTimeout(() => leafletMap.invalidateSize(), 180);
    };

    const fetchRoadRoute = async function (branch, customer) {
        const url = `https://router.project-osrm.org/route/v1/driving/${branch.lng},${branch.lat};${customer.lng},${customer.lat}?overview=full&geometries=geojson&alternatives=true&steps=false`;
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) {
            throw new Error('Failed to fetch route');
        }

        const payload = await response.json();
        const route = (payload?.routes || []).reduce(function (shortest, current) {
            if (!shortest) { return current; }
            return (Number(current?.distance || 0) < Number(shortest?.distance || 0)) ? current : shortest;
        }, null);
        if (!route?.geometry?.coordinates?.length) {
            throw new Error('Route geometry missing');
        }

        return route.geometry.coordinates.map(function (point) {
            return { lat: point[1], lng: point[0] };
        });
    };

    const drawLeafletRoadRoute = async function (branch, customer, requestToken) {
        try {
            const points = await fetchRoadRoute(branch, customer);
            if (requestToken !== routeRequestToken || !leafletMap) { return; }

            if (leafletPolyline) {
                leafletMap.removeLayer(leafletPolyline);
            }

            leafletPolyline = L.polyline(points.map(point => [point.lat, point.lng]), {
                color: '#2563eb',
                weight: 4,
                opacity: 0.9
            }).addTo(leafletMap);

            leafletMap.fitBounds(leafletPolyline.getBounds(), { padding: [40, 40] });
            setTimeout(() => leafletMap.invalidateSize(), 180);
        } catch (error) {
            if (requestToken !== routeRequestToken) { return; }
            fallbackLeafletStraightLine(branch, customer);
        }
    };

    const drawGoogleRoute = function (branch, customer) {
        const mapElement = document.getElementById('orderRouteMapCanvas');
        if (!googleMap) {
            googleMap = new google.maps.Map(mapElement, { center: { lat: branch.lat, lng: branch.lng }, zoom: 13, mapTypeControl: false, streetViewControl: false, fullscreenControl: true });
            googleDirectionsService = new google.maps.DirectionsService();
            googleDirectionsRenderer = new google.maps.DirectionsRenderer({
                map: googleMap,
                suppressMarkers: true,
                preserveViewport: false,
                polylineOptions: { strokeColor: '#2563eb', strokeOpacity: 0.95, strokeWeight: 5 }
            });
        }

        if (googleDirectionsRenderer) {
            googleDirectionsRenderer.set('directions', null);
        }

        if (googleBranchMarker) {
            googleBranchMarker.setMap(null);
            googleBranchMarker = null;
        }

        if (googleCustomerMarker) {
            googleCustomerMarker.setMap(null);
            googleCustomerMarker = null;
        }

        if (googleFallbackPolyline) {
            googleFallbackPolyline.setMap(null);
            googleFallbackPolyline = null;
        }

        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat: branch.lat, lng: branch.lng });
        bounds.extend({ lat: customer.lat, lng: customer.lng });

        googleBranchMarker = new google.maps.Marker({
            map: googleMap,
            position: { lat: branch.lat, lng: branch.lng },
            title: branch.name,
            label: 'A'
        });

        googleCustomerMarker = new google.maps.Marker({
            map: googleMap,
            position: { lat: customer.lat, lng: customer.lng },
            title: customer.name,
            label: 'B'
        });

        const fallbackRoute = async function () {
            try {
                const points = await fetchRoadRoute(branch, customer);
                if (routeRequestToken !== currentRequestToken) { return; }

                googleFallbackPolyline = new google.maps.Polyline({
                    map: googleMap,
                    path: points,
                    geodesic: true,
                    strokeColor: '#2563eb',
                    strokeOpacity: 0.9,
                    strokeWeight: 4
                });
                googleMap.fitBounds(bounds);
                return;
            } catch (error) {
                if (routeRequestToken !== currentRequestToken) { return; }
            }

            googleFallbackPolyline = new google.maps.Polyline({
                map: googleMap,
                path: [
                    { lat: branch.lat, lng: branch.lng },
                    { lat: customer.lat, lng: customer.lng }
                ],
                geodesic: true,
                strokeColor: '#2563eb',
                strokeOpacity: 0.8,
                strokeWeight: 4
            });
            googleMap.fitBounds(bounds);
        };

        const currentRequestToken = ++routeRequestToken;

        googleDirectionsService.route({
            origin: { lat: branch.lat, lng: branch.lng },
            destination: { lat: customer.lat, lng: customer.lng },
            travelMode: google.maps.TravelMode.DRIVING,
            provideRouteAlternatives: true
        }, function (result, status) {
            if (routeRequestToken !== currentRequestToken) { return; }
            if (status === 'OK' && result) {
                const routes = result.routes || [];
                let shortestRouteIndex = 0;
                let shortestDistance = Number.POSITIVE_INFINITY;

                routes.forEach(function (route, index) {
                    const distance = (route.legs || []).reduce(function (total, leg) {
                        return total + Number(leg?.distance?.value || 0);
                    }, 0);

                    if (distance > 0 && distance < shortestDistance) {
                        shortestDistance = distance;
                        shortestRouteIndex = index;
                    }
                });

                googleDirectionsRenderer.setDirections(result);
                googleDirectionsRenderer.setRouteIndex(shortestRouteIndex);

                const route = routes[shortestRouteIndex] || routes[0];
                if (route && route.bounds) {
                    googleMap.fitBounds(route.bounds);
                } else {
                    googleMap.fitBounds(bounds);
                }
                return;
            }

            fallbackRoute();
        });
    };

    $(document).on('click', '.js-show-order-route', function () {
        const button = this;
        const currentRequestToken = ++routeRequestToken;
        const branch = { name: button.dataset.branchName || '-', address: button.dataset.branchAddress || '-', lat: parseFloat(button.dataset.branchLatitude || 0), lng: parseFloat(button.dataset.branchLongitude || 0) };
        const customer = { name: button.dataset.customerName || '-', address: button.dataset.customerAddress || '-', lat: parseFloat(button.dataset.customerLatitude || 0), lng: parseFloat(button.dataset.customerLongitude || 0) };
        const googleUrl = button.dataset.googleUrl || '';
        const distanceValue = parseFloat(button.dataset.distance || 0);

        document.getElementById('orderRouteTitle').textContent = `${@json(__('order_route_preview'))} - ${button.dataset.orderNumber || '#'}`;
        document.getElementById('orderRouteMeta').textContent = `${branch.name} -> ${customer.name}`;
        document.getElementById('routeBranchName').textContent = branch.name;
        document.getElementById('routeBranchAddress').textContent = branch.address;
        document.getElementById('routeCustomerName').textContent = customer.name;
        document.getElementById('routeCustomerAddress').textContent = customer.address;
        document.getElementById('routeBranchCoordinates').textContent = `${branch.lat.toFixed(6)}, ${branch.lng.toFixed(6)}`;
        document.getElementById('routeCustomerCoordinates').textContent = `${customer.lat.toFixed(6)}, ${customer.lng.toFixed(6)}`;
        document.getElementById('routeDistanceText').textContent = Number.isFinite(distanceValue) && distanceValue > 0 ? `${distanceValue.toFixed(2)} km` : @json(__('distance_not_available'));

        if (googleUrl) {
            routeGoogleMapsButton.href = googleUrl;
            routeGoogleMapsButton.classList.remove('d-none');
        } else {
            routeGoogleMapsButton.href = '#';
            routeGoogleMapsButton.classList.add('d-none');
        }

        routeModal.show();
        setTimeout(function () {
            if (@json($canUseGoogleMaps) && window.google?.maps) { drawGoogleRoute(branch, customer); return; }
            drawLeafletRoute(branch, customer);
            drawLeafletRoadRoute(branch, customer, currentRequestToken);
        }, 220);
    });

    $(document).on('click', '.js-open-order-invoice', function () {
        const button = this;
        const invoiceUrl = button.dataset.url || '';

        if (! invoiceUrl || ! invoiceFrame) {
            return;
        }

        invoiceTitle.textContent = `${@json(__('invoice'))} - ${button.dataset.orderNumber || '#'}`;
        invoiceFrame.src = invoiceUrl;
        invoiceModal.show();
    });

    $(document).on('click', '.js-open-order-status-modal', function () {
        const button = this;
        orderStatusModalTitle.textContent = `${@json(__('status'))} - ${button.dataset.orderNumber || '#'}`;
        orderStatusUpdateUrl.value = button.dataset.url || '';
        orderStatusSelect.value = button.dataset.currentStatus || '';
        orderStatusPaymentMethod = button.dataset.paymentMethod || '';
        orderStatusPaymentStatus = button.dataset.paymentStatus || '';
        syncOrderStatusPaymentConfirmation();
        statusModal.show();
    });

    orderStatusSelect?.addEventListener('change', syncOrderStatusPaymentConfirmation);

    $(document).on('click', '.js-open-driver-assign-modal', function () {
        const button = this;
        const orderId = button.dataset.orderId || '';
        const orderNumber = button.dataset.orderNumber || '#';
        if (!orderId) { return; }

        assignDriverModalTitle.textContent = `Assign Driver - ${orderNumber}`;
        assignDriverOrderId.value = orderId;
        assignDriverSelect.innerHTML = '<option value="">Loading drivers...</option>';
        assignDriverSaveButton.disabled = true;
        assignDriverModal.show();

        $.ajax({
            url: `{{ url('/orders') }}/${orderId}/available-drivers`,
            type: 'GET',
            success: function (response) {
                const drivers = Array.isArray(response.drivers) ? response.drivers : [];
                const assignedId = response.assigned_driver_id ? String(response.assigned_driver_id) : '';
                if (!drivers.length) {
                    assignDriverSelect.innerHTML = '<option value="">No drivers available for this branch</option>';
                    assignDriverSaveButton.disabled = true;
                    return;
                }

                assignDriverSelect.innerHTML = '<option value="">Select driver</option>' + drivers.map(driver => {
                    const selected = assignedId === String(driver.id) ? 'selected' : '';
                    return `<option value="${driver.id}" ${selected}>${driver.name}${driver.email ? ' - ' + driver.email : ''}</option>`;
                }).join('');
                assignDriverSaveButton.disabled = false;
            },
            error: function () {
                assignDriverSelect.innerHTML = '<option value="">Unable to load drivers</option>';
                assignDriverSaveButton.disabled = true;
            }
        });
    });

    $(document).on('click', '.js-open-branch-change-modal', function () {
        const button = this;
        const orderId = button.dataset.orderId || '';
        const orderNumber = button.dataset.orderNumber || '#';
        if (!orderId) { return; }

        changeBranchModalTitle.textContent = `${@json(__('change_branch'))} - ${orderNumber}`;
        changeBranchOrderId.value = orderId;
        changeBranchSelect.innerHTML = `<option value="">${@json(__('loading'))}</option>`;
        changeBranchSaveButton.disabled = true;
        changeBranchModal.show();
        initBranchSelect();

        $.ajax({
            url: `{{ url('/orders') }}/${orderId}/available-branches`,
            type: 'GET',
            success: function (response) {
                const branches = Array.isArray(response.branches) ? response.branches : [];
                const selectedId = response.selected_branch_id ? String(response.selected_branch_id) : '';

                if (!branches.length) {
                    changeBranchSelect.innerHTML = '<option value="">No branches available</option>';
                    initBranchSelect();
                    changeBranchSaveButton.disabled = true;
                    return;
                }

                changeBranchSelect.innerHTML = '<option value=""></option>' + branches.map(branch => {
                    const selected = selectedId === String(branch.id) ? 'selected' : '';
                    const details = [branch.location, branch.address].filter(Boolean).join(' - ');
                    return `<option value="${branch.id}" ${selected}>${branch.name}${details ? ' - ' + details : ''}</option>`;
                }).join('');
                initBranchSelect();
                changeBranchSaveButton.disabled = false;
            },
            error: function () {
                changeBranchSelect.innerHTML = '<option value="">Unable to load branches</option>';
                initBranchSelect();
                changeBranchSaveButton.disabled = true;
            }
        });
    });

    orderStatusForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const updateUrl = orderStatusUpdateUrl.value;
        const nextValue = orderStatusSelect.value;
        if (!updateUrl || !nextValue) {
            return;
        }
        const defaultText = orderStatusSaveButton.textContent;
        orderStatusSaveButton.disabled = true;
        orderStatusSaveButton.textContent = @json(__('loading'));

        updateOrderStatus(updateUrl, nextValue, {
            confirm_payment_received: orderStatusPaymentConfirm?.checked ? 1 : 0
        }, function () {
            orderStatusSaveButton.disabled = false;
            orderStatusSaveButton.textContent = defaultText;
            statusModal.hide();
            table.ajax.reload(null, false);
            Swal.fire({
                icon: 'success',
                title: @json(__('success')),
                text: @json(__('order_status_updated_successfully')),
                timer: 2000,
                showConfirmButton: false,
                padding: '2em'
            });
        }, function (xhr) {
            orderStatusSaveButton.disabled = false;
            orderStatusSaveButton.textContent = defaultText;
            Swal.fire({
                icon: 'error',
                title: @json(__('error')),
                text: xhr.responseJSON?.message || @json(__('order_status_update_failed')),
                confirmButtonText: @json(__('ok'))
            });
        });
    });

    $(document).on('click', '.js-mark-order-paid', function () {
        const button = this;
        const updateUrl = button.dataset.url || '';
        const orderNumber = button.dataset.orderNumber || '#';
        const paymentMethodLabel = button.dataset.paymentMethodLabel || @json(__('payment_method'));
        if (!updateUrl) { return; }

        Swal.fire({
            icon: 'question',
            title: @json(__('mark_as_paid')),
            text: `${@json(__('mark_order_paid_confirmation'))} (${orderNumber} - ${paymentMethodLabel})`,
            showCancelButton: true,
            confirmButtonText: @json(__('yes_mark_paid')),
            cancelButtonText: @json(__('cancel'))
        }).then((result) => {
            if (!result.isConfirmed) { return; }

            $.ajax({
                url: updateUrl,
                type: 'PATCH',
                data: { _token: @json(csrf_token()) },
                success: function (response) {
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: @json(__('success')),
                        text: response.message || @json(__('order_marked_paid_successfully')),
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: @json(__('error')),
                        text: xhr.responseJSON?.message || @json(__('unable_to_mark_order_paid')),
                        confirmButtonText: @json(__('ok'))
                    });
                }
            });
        });
    });

    assignDriverForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const orderId = assignDriverOrderId.value;
        const driverId = assignDriverSelect.value;
        if (!orderId || !driverId) {
            return;
        }

        const defaultText = assignDriverSaveButton.textContent;
        assignDriverSaveButton.disabled = true;
        assignDriverSaveButton.textContent = 'Assigning...';

        $.ajax({
            url: `{{ url('/orders') }}/${orderId}/assign-driver`,
            type: 'PATCH',
            data: { _token: @json(csrf_token()), driver_id: driverId },
            success: function () {
                assignDriverSaveButton.disabled = false;
                assignDriverSaveButton.textContent = defaultText;
                assignDriverModal.hide();
                table.ajax.reload(null, false);
                Swal.fire({
                    icon: 'success',
                    title: @json(__('success')),
                    text: 'Driver assigned successfully.',
                    timer: 2000,
                    showConfirmButton: false,
                    padding: '2em'
                });
            },
            error: function (xhr) {
                assignDriverSaveButton.disabled = false;
                assignDriverSaveButton.textContent = defaultText;
                Swal.fire({
                    icon: 'error',
                    title: @json(__('error')),
                    text: xhr.responseJSON?.message || 'Unable to assign driver right now.',
                    confirmButtonText: @json(__('ok'))
                });
            }
        });
    });

    changeBranchForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const orderId = changeBranchOrderId.value;
        const organizationId = changeBranchSelect.value;
        if (!orderId || !organizationId) {
            return;
        }

        const defaultText = changeBranchSaveButton.textContent;
        changeBranchSaveButton.disabled = true;
        changeBranchSaveButton.textContent = @json(__('loading'));

        $.ajax({
            url: `{{ url('/orders') }}/${orderId}/change-branch`,
            type: 'PATCH',
            data: { _token: @json(csrf_token()), organization_id: organizationId },
            success: function (response) {
                changeBranchSaveButton.disabled = false;
                changeBranchSaveButton.textContent = defaultText;
                changeBranchModal.hide();
                table.ajax.reload(null, false);
                Swal.fire({
                    icon: 'success',
                    title: @json(__('success')),
                    text: response.message || @json(__('order_branch_updated_successfully')),
                    timer: 2200,
                    showConfirmButton: false,
                    padding: '2em'
                });
            },
            error: function (xhr) {
                changeBranchSaveButton.disabled = false;
                changeBranchSaveButton.textContent = defaultText;
                Swal.fire({
                    icon: 'error',
                    title: @json(__('error')),
                    text: xhr.responseJSON?.message || @json(__('selected_branch_is_not_available')),
                    confirmButtonText: @json(__('ok'))
                });
            }
        });
    });

    routeModalElement.addEventListener('shown.bs.modal', function () { if (leafletMap) { leafletMap.invalidateSize(); } });
    invoiceModalElement.addEventListener('hidden.bs.modal', function () {
        if (invoiceFrame) {
            invoiceFrame.src = 'about:blank';
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initOrdersManagePage, { once: true });
} else {
    window.initOrdersManagePage();
}
</script>
@if($canUseGoogleMaps)
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsApiKey) }}&callback=initOrdersManagePage"></script>
@endif
@endpush
