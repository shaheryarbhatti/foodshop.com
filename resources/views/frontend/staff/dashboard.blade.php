@extends('layouts.frontend')

@section('title', 'Staff Dashboard')

@php
    $activeNav = 'staff-dashboard';
    $canUseGoogleMaps = $mapProvider === 'google' && filled($googleMapsApiKey);
    $userImage = $portalUser->image ? asset('public/storage/' . $portalUser->image) : null;
@endphp

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
<style>
.staff-dashboard{padding:34px 0 56px}.staff-main{border-radius:28px;background:rgba(255,255,255,.96);border:1px solid rgba(15,23,42,.08);box-shadow:0 24px 60px rgba(15,23,42,.12);padding:28px}.staff-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}.staff-hero h1{margin:0;color:#0f172a;font-size:clamp(2rem,3vw,2.7rem);font-weight:900;letter-spacing:-.04em}.staff-hero p{margin:10px 0 0;color:#64748b;max-width:760px;font-size:1rem;line-height:1.7}.staff-hero-actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:flex-end}.staff-logout-form{margin:0}.staff-logout-btn{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border:0;border-radius:999px;background:linear-gradient(135deg,#ef4444,#f97316);color:#fff;font-weight:900;box-shadow:0 14px 26px rgba(239,68,68,.18)}.staff-hero-pill{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border-radius:999px;background:linear-gradient(135deg,rgba(203,43,29,.14),rgba(255,191,31,.2));color:#8d411e;font-weight:900}.planner-trigger{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border:0;border-radius:999px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;font-weight:900;box-shadow:0 14px 26px rgba(15,23,42,.16)}.staff-cards{margin-top:24px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}.order-status-card{position:relative;display:flex;align-items:center;gap:18px;min-height:126px;padding:24px 22px;border-radius:24px;border:0;box-shadow:0 18px 34px rgba(15,23,42,.08);transition:transform .2s ease,box-shadow .2s ease,outline-color .2s ease;outline:2px solid transparent}.order-status-card:hover,.order-status-card.is-active{transform:translateY(-3px);box-shadow:0 24px 44px rgba(15,23,42,.12);outline-color:rgba(15,23,42,.14)}.order-status-card__icon{display:inline-flex;align-items:center;justify-content:center;width:74px;height:74px;border-radius:50%;background:rgba(255,255,255,.88);color:#1f2937;font-size:1.9rem;box-shadow:inset 0 0 0 1px rgba(255,255,255,.7)}.order-status-card__content{display:flex;flex-direction:column;gap:8px}.order-status-card__title{color:#334155;font-size:1.1rem;font-weight:700}.order-status-card__count{color:#0f172a;font-size:2rem;font-weight:800;line-height:1;letter-spacing:-.03em}.order-card-total{background:linear-gradient(135deg,#b8cff2,#9fbee8)}.order-card-pending{background:linear-gradient(135deg,#fff0a8,#fee786)}.order-card-processing{background:linear-gradient(135deg,#c8eceb,#b0e0df)}.order-card-shipped{background:linear-gradient(135deg,#ffd7b4,#f8c89f)}.order-card-delivered{background:linear-gradient(135deg,#f4d8ea,#edcfe1)}.order-card-cancelled{background:linear-gradient(135deg,#ffd89d,#f8cb80)}.order-card-returned{background:linear-gradient(135deg,#c9f0a8,#b7e792)}.order-card-failed{background:linear-gradient(135deg,#c8e9fb,#b5ddf3)}.staff-manage-card{margin-top:22px;padding:24px;border-radius:26px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 18px 40px rgba(15,23,42,.06)}.staff-manage-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;margin-bottom:18px}.staff-manage-head h2{margin:0 0 8px;color:#0f172a;font-size:2rem;font-weight:900}.staff-manage-head p{margin:0;color:#64748b;line-height:1.7;max-width:720px}.staff-filter-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff7ed;border:1px solid rgba(234,88,12,.12);color:#9a3412;font-size:.86rem;font-weight:900}.frontend-action-row{display:flex;flex-wrap:wrap;gap:8px}.frontend-action-btn{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;border:0;color:#fff;text-decoration:none;box-shadow:0 10px 18px rgba(15,23,42,.1)}.frontend-action-btn--success{background:linear-gradient(135deg,#10b981,#14b8a6)}.frontend-action-btn--dark{background:linear-gradient(135deg,#0f172a,#334155)}.frontend-action-btn--info{background:linear-gradient(135deg,#0891b2,#38bdf8)}.frontend-action-btn--primary{background:linear-gradient(135deg,#2563eb,#3b82f6)}.frontend-action-btn--warning{background:linear-gradient(135deg,#f59e0b,#fb923c)}.frontend-action-btn--danger{background:linear-gradient(135deg,#ef4444,#f97316)}.frontend-action-btn[disabled]{opacity:.45;cursor:not-allowed;box-shadow:none}.table-shell{overflow:hidden;border-radius:22px;border:1px solid rgba(15,23,42,.08)}table.dataTable{margin-top:0!important}.dataTables_wrapper .dataTables_filter input,.dataTables_wrapper .dataTables_length select{border-radius:12px!important;border:1px solid rgba(15,23,42,.12)!important;min-height:42px}.dataTables_wrapper .dataTables_filter{margin-bottom:16px}.dataTables_wrapper .dataTables_info{padding-top:16px;color:#64748b;font-weight:700}.dataTables_wrapper .dataTables_paginate .paginate_button{padding:0!important;border:0!important;background:transparent!important}.dataTables_wrapper .dataTables_paginate .page-link{border-radius:10px!important;border:1px solid rgba(15,23,42,.1)!important;color:#475569!important}.dataTables_wrapper .dataTables_paginate .active .page-link{background:#0f172a!important;border-color:#0f172a!important;color:#fff!important}.order-route-modal .modal-content,.order-invoice-modal,.order-status-modal,.route-planner-modal .modal-content,.assign-driver-modal .modal-content{border:0;border-radius:28px;box-shadow:0 30px 80px rgba(15,23,42,.2);overflow:hidden}.assign-driver-modal{max-width:min(680px,calc(100vw - 32px))}.assign-driver-modal .modal-body{overflow:hidden}.assign-driver-form{display:grid;grid-template-columns:minmax(0,1fr);gap:18px;width:100%}.assign-driver-form>*{min-width:0}.assign-driver-helper{width:100%;max-width:100%;padding:14px 16px;border-radius:16px;background:#eff6ff;color:#1d4ed8;font-weight:700;line-height:1.6;overflow-wrap:anywhere}.assign-driver-form .text-end{width:100%}.assign-driver-form .select2-container{max-width:100%!important}.assign-driver-form .select2-selection--single{width:100%;max-width:100%}.assign-driver-form .select2-selection__rendered{display:block;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:28px}.order-route-modal__eyebrow{display:inline-flex;padding:6px 12px;margin-bottom:8px;border-radius:999px;background:rgba(59,130,246,.1);color:#2563eb;font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.order-invoice-frame-wrap{border-radius:24px;overflow:hidden;border:1px solid rgba(15,23,42,.08);background:#f8fafc}.order-invoice-frame{display:block;width:100%;min-height:82vh;border:0;background:#fff}.route-info-card,.route-map-shell,.planner-side,.planner-map-shell{height:100%;border-radius:24px;background:linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid rgba(15,23,42,.08)}.route-info-card,.planner-side{padding:24px}.route-party-card{display:flex;gap:14px;align-items:flex-start;padding:16px;border-radius:18px;background:#fff;border:1px solid rgba(15,23,42,.07);box-shadow:0 12px 24px rgba(15,23,42,.05)}.route-party-card+.route-party-card{margin-top:18px}.route-party-card__icon{display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:14px;font-size:1.15rem;flex-shrink:0}.route-party-card__label{color:#64748b;font-size:.76rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}.route-party-card__title{color:#0f172a;font-size:1rem;font-weight:800;margin-bottom:4px}.route-party-card__text{color:#475569;line-height:1.55}.route-connector{display:flex;align-items:center;gap:12px;margin:18px 0;color:#64748b;font-size:.85rem;font-weight:700}.route-connector span{flex:1;height:1px;background:linear-gradient(90deg,rgba(148,163,184,.1),rgba(148,163,184,.7),rgba(148,163,184,.1))}.route-coordinates{display:grid;gap:12px;margin-top:20px}.route-coordinates div{padding:14px 16px;border-radius:16px;background:rgba(15,23,42,.03)}.route-coordinates span{display:block;color:#64748b;font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px}.route-coordinates strong{color:#0f172a;font-size:.95rem}.route-map-shell__head,.planner-map-head{padding:22px 24px 0}#orderRouteMapCanvas,#routePlannerMapCanvas{height:480px;margin:20px;border-radius:20px;overflow:hidden;background:linear-gradient(180deg,#eff6ff,#f8fafc)}.portal-alert{padding:14px 16px;border-radius:16px;font-weight:700;margin-bottom:18px}.portal-alert--success{background:#dcfce7;color:#166534}.portal-alert--error{background:#fee2e2;color:#991b1b}.planner-toolbar{display:grid;gap:14px}.planner-origin-toggle{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.planner-origin-btn{border:1px solid rgba(15,23,42,.08);background:#fff;border-radius:16px;padding:12px 14px;font-weight:800;color:#334155}.planner-origin-btn.is-active{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-color:#0f172a}.planner-status{padding:12px 14px;border-radius:16px;background:#eff6ff;color:#1d4ed8;font-weight:700}.planner-status.is-error{background:#fee2e2;color:#b91c1c}.planner-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:18px}.planner-stat{padding:14px 16px;border-radius:18px;background:#fff;border:1px solid rgba(15,23,42,.07)}.planner-stat span{display:block;color:#64748b;font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px}.planner-stat strong{color:#0f172a;font-size:1.1rem}.planner-orders{margin-top:18px;display:grid;gap:10px;max-height:340px;overflow:auto;padding-right:4px}.planner-order{padding:14px 16px;border-radius:18px;background:#fff;border:1px solid rgba(15,23,42,.07)}.planner-order strong{display:block;color:#0f172a;margin-bottom:4px}.planner-order span{display:block;color:#64748b;font-size:.9rem;line-height:1.5}.planner-order small{display:inline-flex;margin-top:8px;padding:6px 10px;border-radius:999px;background:#fff7ed;color:#9a3412;font-weight:800}.planner-empty{padding:22px;border-radius:18px;background:#f8fafc;border:1px dashed rgba(15,23,42,.12);text-align:center;color:#64748b;font-weight:700}@media (max-width:1199.98px){.staff-cards{grid-template-columns:repeat(3,minmax(0,1fr))}}@media (max-width:991.98px){.staff-cards{grid-template-columns:repeat(2,minmax(0,1fr))}#orderRouteMapCanvas,#routePlannerMapCanvas{height:360px}}@media (max-width:767.98px){.staff-dashboard{padding:18px 0 32px}.staff-main{padding:18px;border-radius:22px}.staff-cards,.planner-origin-toggle,.planner-stats{grid-template-columns:1fr}.staff-manage-head h2{font-size:1.55rem}.order-status-card{min-height:112px;padding:20px 18px}.order-status-card__icon{width:62px;height:62px;font-size:1.45rem}.order-status-card__count{font-size:1.7rem}.staff-hero-actions{width:100%}.staff-logout-btn,.planner-trigger,.staff-hero-pill{width:100%;justify-content:center}.assign-driver-modal{max-width:calc(100vw - 24px)}}
.single-route-toolbar{display:grid;gap:14px;margin-bottom:18px}.single-route-origin-toggle{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.single-route-origin-btn{border:1px solid rgba(15,23,42,.08);background:#fff;border-radius:16px;padding:12px 14px;font-weight:800;color:#334155}.single-route-origin-btn.is-active{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-color:#0f172a}.single-route-status{padding:12px 14px;border-radius:16px;background:#eff6ff;color:#1d4ed8;font-weight:700}.single-route-status.is-error{background:#fee2e2;color:#b91c1c}@media (max-width:767.98px){.single-route-origin-toggle{grid-template-columns:1fr}}.select2-container{width:100%!important}.select2-container .select2-selection--single{min-height:46px;border-radius:12px;border-color:rgba(15,23,42,.12);padding:8px 12px}.select2-container .select2-selection--single .select2-selection__rendered{line-height:28px;padding-left:0}.select2-container .select2-selection--single .select2-selection__arrow{height:44px}
</style>
@endsection

@section('content')
<section class="staff-dashboard">
    <div class="container">
        <div class="staff-main">
                <div class="staff-hero">
                    <div>
                        <h1>{{ $portalRoleLabel }} Dashboard</h1>
                        <p>Monitor every incoming order, move statuses forward in real time, open invoices, and jump into order edits without leaving the frontend portal.</p>
                    </div>
                    <div class="staff-hero-actions">
                        <form method="POST" action="{{ route('frontend.logout') }}" class="staff-logout-form">
                            @csrf
                            <button type="submit" class="staff-logout-btn">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>{{ __('logout') }}</span>
                            </button>
                        </form>
                        <button type="button" class="planner-trigger" id="openRoutePlannerButton">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <span>Route Planner</span>
                        </button>
                        <div class="staff-hero-pill">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Live order operations from the frontend side</span>
                        </div>
                    </div>
                </div>

                <div class="staff-cards">
                    @foreach($statusCards as $card)
                        <button type="button" class="order-status-card {{ $card['theme'] }} js-order-filter-card {{ $card['key'] === 'all' ? 'is-active' : '' }}" data-status="{{ $card['key'] }}">
                            <span class="order-status-card__icon"><i class="fa {{ $card['icon'] }}"></i></span>
                            <span class="order-status-card__content">
                                <span class="order-status-card__title">{{ $card['title'] }}</span>
                                <span class="order-status-card__count">{{ number_format((int) $card['count']) }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="staff-manage-card">
                    @if (session('success'))
                        <div class="portal-alert portal-alert--success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="portal-alert portal-alert--error">{{ session('error') }}</div>
                    @endif

                    <div class="staff-manage-head">
                        <div>
                            <h2>Manage Orders</h2>
                            <p>Pick a status card to instantly filter the listing below. The selected tab controls which orders appear in the table, while the action buttons keep invoice, route, status, edit, and delete tools one click away.</p>
                        </div>
                        <span class="staff-filter-badge" id="activeOrderFilterLabel">{{ __('all_orders') }}</span>
                    </div>

                    <div class="table-shell">
                        <div class="table-responsive">
                            <table id="ordersTable" class="table table-hover align-middle mb-0" style="width:100%">
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
</section>

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
                            <div class="single-route-toolbar">
                                <div class="single-route-origin-toggle">
                                    <button type="button" class="single-route-origin-btn is-active" data-single-origin-mode="branch">Start from Branch</button>
                                    <button type="button" class="single-route-origin-btn" data-single-origin-mode="current">Use My Location</button>
                                </div>
                                <div class="single-route-status" id="singleRouteStatusMessage">This route currently starts from the assigned branch.</div>
                            </div>
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
                    <small class="text-muted">Frontend order portal</small>
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
                    <small class="text-muted">Update order progress</small>
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
                    <div class="portal-alert portal-alert--error d-none" id="orderStatusPaymentNotice">{{ __('offline_payment_delivery_notice') }}</div>
                    <div class="form-check mb-3 d-none" id="orderStatusPaymentConfirmWrap">
                        <input class="form-check-input" type="checkbox" id="orderStatusPaymentConfirm">
                        <label class="form-check-label fw-semibold" for="orderStatusPaymentConfirm">{{ __('confirm_payment_received_checkbox') }}</label>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-dark" id="orderStatusSaveButton">{{ __('save_settings') }}</button>
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
                    <small class="text-muted">Choose a driver from the same branch as this order.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="assignDriverForm">
                    <input type="hidden" id="assignDriverOrderId">
                    <div class="mb-3">
                        <label for="assignDriverSelect" class="form-label fw-semibold">Driver</label>
                        <select id="assignDriverSelect" class="form-select">
                            <option value="">Select driver</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-dark" id="assignDriverSaveButton">Assign Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="routePlannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content route-planner-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">Multi Order Planner</div>
                    <h5 class="modal-title mb-1">Order Location Planner</h5>
                    <small class="text-muted">View all assigned order locations and calculate the shortest route from the branch or your live location.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="planner-side">
                            <div class="planner-toolbar">
                                <div class="planner-origin-toggle">
                                    <button type="button" class="planner-origin-btn is-active" data-origin-mode="branch">Start from Branch</button>
                                    <button type="button" class="planner-origin-btn" data-origin-mode="current">Use My Location</button>
                                </div>
                                <div class="planner-status" id="plannerStatusMessage">Routes will start from each order branch by default.</div>
                            </div>

                            <div class="planner-stats">
                                <div class="planner-stat">
                                    <span>Orders Mapped</span>
                                    <strong id="plannerOrderCount">0</strong>
                                </div>
                                <div class="planner-stat">
                                    <span>Total Shortest Distance</span>
                                    <strong id="plannerDistanceTotal">0.00 km</strong>
                                </div>
                            </div>

                            <div class="planner-orders" id="plannerOrderList">
                                <div class="planner-empty">Open the planner to load assigned orders on the map.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="planner-map-shell">
                            <div class="planner-map-head">
                                <h6 class="mb-1">Route Map</h6>
                                <small class="text-muted">Every order pin is shown on the map, and each route is drawn using the shortest path available for the selected starting point.</small>
                            </div>
                            <div id="routePlannerMapCanvas"></div>
                        </div>
                    </div>
</div>
</div>
</div>
</div>
</div>
<div class="modal fade" id="assignDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content assign-driver-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">Driver Assignment</div>
                    <h5 class="modal-title mb-1" id="assignDriverModalTitle">Assign Driver</h5>
                    <small class="text-muted">Only drivers linked to the same branch will appear here.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="assignDriverForm" class="assign-driver-form">
                    <input type="hidden" id="assignDriverOrderId">
                    <div class="assign-driver-helper">
                        Staff can assign the selected order to a branch-matched driver without leaving the dashboard.
                    </div>
                    <div>
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
        <div class="modal-content assign-driver-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="order-route-modal__eyebrow">{{ __('change_branch') }}</div>
                    <h5 class="modal-title mb-1" id="changeBranchModalTitle">{{ __('change_branch') }}</h5>
                    <small class="text-muted">{{ __('change_branch_modal_hint') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="changeBranchForm" class="assign-driver-form">
                    <input type="hidden" id="changeBranchOrderId">
                    <div class="assign-driver-helper">{{ __('branch_change_notice') }}</div>
                    <div>
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

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
@if($canUseGoogleMaps)
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsApiKey) }}&loading=async"></script>
@endif
<script>
window.initFrontendStaffDashboard = function () {
    if (window.__frontendStaffDashboardInitialized) { return; }
    window.__frontendStaffDashboardInitialized = true;

    const canUseGoogleMaps = @json($canUseGoogleMaps);
    const isDriverPortal = @json($portalUser->hasRole('Driver'));
    const csrfToken = @json(csrf_token());
    const driverOfferAcceptUrlTemplate = @json(route('frontend.staff.orders.driver-offer.accept', ['order' => '__ORDER__']));
    const driverOfferRejectUrlTemplate = @json(route('frontend.staff.orders.driver-offer.reject', ['order' => '__ORDER__']));
    const statusLabels = @json(['all' => __('all_orders')] + $statusOptions);
    const table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: '{{ route('frontend.staff.dashboard') }}',
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
        language: { processing: '<div class="spinner-border text-dark" role="status"><span class="visually-hidden">Loading...</span></div>' },
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

    $(document).on('submit', '.js-confirm-delete', function (event) {
        if (!window.confirm('Are you sure you want to delete this order?')) {
            event.preventDefault();
        }
    });

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
    const plannerModalElement = document.getElementById('routePlannerModal');
    const plannerModal = new bootstrap.Modal(plannerModalElement);
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
    const singleRouteStatusMessage = document.getElementById('singleRouteStatusMessage');
    const singleOriginButtons = document.querySelectorAll('[data-single-origin-mode]');
    const openRoutePlannerButton = document.getElementById('openRoutePlannerButton');
    const plannerStatusMessage = document.getElementById('plannerStatusMessage');
    const plannerOrderCount = document.getElementById('plannerOrderCount');
    const plannerDistanceTotal = document.getElementById('plannerDistanceTotal');
    const plannerOrderList = document.getElementById('plannerOrderList');
    const plannerOriginButtons = document.querySelectorAll('[data-origin-mode]');
    let leafletMap;
    let leafletMarkers = [];
    let leafletPolyline;
    let plannerLeafletMap;
    let plannerLeafletLayers = [];
    let plannerGoogleMap;
    let plannerGoogleObjects = [];
    let plannerOriginMode = 'branch';
    let plannerCurrentLocation = null;
    let orderRouteGoogleMap;
    let orderRouteGoogleObjects = [];
    let singleRouteOriginMode = 'branch';
    let singleRouteCurrentLocation = null;
    let activeSingleRoute = null;
    let notificationAudioContext = null;
    let notificationAudioUnlocked = false;
    let activeNotificationBeepTimer = null;
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

    const ensureNotificationAudio = function () {
        if (notificationAudioContext || !window.AudioContext) { return; }
        notificationAudioContext = new window.AudioContext();
    };

    const unlockNotificationAudio = async function () {
        ensureNotificationAudio();
        if (!notificationAudioContext) { return; }

        try {
            if (notificationAudioContext.state === 'suspended') {
                await notificationAudioContext.resume();
            }
            notificationAudioUnlocked = true;
        } catch (error) {
            notificationAudioUnlocked = false;
        }
    };

    const playNotificationBeep = async function () {
        await unlockNotificationAudio();
        if (!notificationAudioContext || !notificationAudioUnlocked) { return; }

        const oscillator = notificationAudioContext.createOscillator();
        const gainNode = notificationAudioContext.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(1046.5, notificationAudioContext.currentTime);
        gainNode.gain.setValueAtTime(0.0001, notificationAudioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.12, notificationAudioContext.currentTime + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.0001, notificationAudioContext.currentTime + 0.28);
        oscillator.connect(gainNode);
        gainNode.connect(notificationAudioContext.destination);
        oscillator.start();
        oscillator.stop(notificationAudioContext.currentTime + 0.30);
    };

    const stopRepeatingNotificationBeep = function () {
        if (activeNotificationBeepTimer) {
            window.clearInterval(activeNotificationBeepTimer);
            activeNotificationBeepTimer = null;
        }
    };

    const startRepeatingNotificationBeep = function () {
        stopRepeatingNotificationBeep();
        playNotificationBeep();
        activeNotificationBeepTimer = window.setInterval(() => {
            playNotificationBeep();
        }, 900);
    };

    const postDriverOfferAction = async function (url) {
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ _token: csrfToken }).toString(),
            credentials: 'same-origin'
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(payload.message || 'Unable to update this driver request right now.');
        }

        return payload;
    };

    const showStaffOrderAlert = function (title, message) {
        if (typeof Swal === 'undefined') { return; }

        startRepeatingNotificationBeep();
        Swal.fire({
            title: title || 'New order received',
            html: `
                <div style="display:flex;align-items:flex-start;gap:14px;text-align:left;">
                    <div style="width:52px;height:52px;flex:0 0 52px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0f172a,#1e293b);color:#ffffff;font-size:1.15rem;box-shadow:0 16px 28px rgba(15,23,42,.18);">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <div style="font-size:1rem;font-weight:900;color:#0f172a;margin-bottom:6px;">${escapeHtml(title || 'Branch order alert')}</div>
                        <div style="color:#475569;line-height:1.7;">${escapeHtml(message || 'A new branch order just arrived and is ready for review.')}</div>
                    </div>
                </div>
            `,
            icon: null,
            showConfirmButton: true,
            confirmButtonText: 'Okay',
            showCloseButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'shadow-lg rounded-4',
                confirmButton: 'btn btn-dark px-4'
            },
            buttonsStyling: false,
            backdrop: 'rgba(15,23,42,0.55)'
        }).then(function (result) {
            stopRepeatingNotificationBeep();
            if (result.isConfirmed) {
                window.location.reload();
            }
        });
    };

    const showDriverOfferAlert = function (title, message, orderId) {
        if (typeof Swal === 'undefined' || !orderId) { return; }

        const acceptUrl = driverOfferAcceptUrlTemplate.replace('__ORDER__', orderId);
        const rejectUrl = driverOfferRejectUrlTemplate.replace('__ORDER__', orderId);

        startRepeatingNotificationBeep();
        Swal.fire({
            title: title || 'New delivery request',
            html: `
                <div style="display:flex;align-items:flex-start;gap:14px;text-align:left;">
                    <div style="width:56px;height:56px;flex:0 0 56px;border-radius:18px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0f172a,#1e293b);color:#ffffff;font-size:1.2rem;box-shadow:0 16px 28px rgba(15,23,42,.18);">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                    <div>
                        <div style="font-size:1rem;font-weight:900;color:#0f172a;margin-bottom:6px;">${escapeHtml(title || 'New delivery request')}</div>
                        <div style="color:#475569;line-height:1.7;">${escapeHtml(message || 'A new order is available for your branch. Would you like to take it now?')}</div>
                    </div>
                </div>
            `,
            icon: null,
            showConfirmButton: true,
            showDenyButton: true,
            confirmButtonText: @json(__('driver_accept_offer')),
            denyButtonText: @json(__('driver_reject_offer')),
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCloseButton: false,
            customClass: {
                popup: 'shadow-lg rounded-4',
                confirmButton: 'btn btn-dark px-4',
                denyButton: 'btn btn-outline-secondary px-4'
            },
            buttonsStyling: false,
            backdrop: 'rgba(15,23,42,0.55)',
            preConfirm: async function () {
                try {
                    return await postDriverOfferAction(acceptUrl);
                } catch (error) {
                    Swal.showValidationMessage(error.message);
                    return false;
                }
            },
            preDeny: async function () {
                try {
                    return await postDriverOfferAction(rejectUrl);
                } catch (error) {
                    Swal.showValidationMessage(error.message);
                    return false;
                }
            }
        }).then(function (result) {
            stopRepeatingNotificationBeep();
            if (result.isConfirmed || result.isDenied) {
                window.location.reload();
            }
        });
    };

    const pollStaffNotifications = async function () {
        const notificationEndpoint = @json(route('frontend.staff.notifications.summary'));
        const previousLatestNotificationId = Number(window.__staffLatestNotificationId || @json($portalLatestNotificationId));

        try {
            const response = await fetch(notificationEndpoint, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) { return; }

            const payload = await response.json();
            const incomingLatestId = Number(payload.latest_id || 0);
            if (incomingLatestId > previousLatestNotificationId) {
                window.__staffLatestNotificationId = incomingLatestId;
                if (isDriverPortal && payload.latest_type === 'driver_offer' && payload.latest_order_id) {
                    showDriverOfferAlert(
                        payload.latest_title || 'New delivery request',
                        payload.latest_message || 'A new order is available for your branch. Would you like to take it now?',
                        payload.latest_order_id
                    );
                    return;
                }

                showStaffOrderAlert(
                    payload.latest_title || 'New order received',
                    payload.latest_message || 'A new order has been received in your assigned branch.'
                );
            }
        } catch (error) {
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

    const fetchRoadRoute = async function (branch, customer) {
        const url = `https://router.project-osrm.org/route/v1/driving/${branch.lng},${branch.lat};${customer.lng},${customer.lat}?overview=full&geometries=geojson&alternatives=true&steps=false`;
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) { throw new Error('Failed to fetch route'); }
        const payload = await response.json();
        const route = (payload?.routes || []).reduce(function (shortest, current) {
            if (!shortest) { return current; }
            return Number(current?.distance || 0) < Number(shortest?.distance || 0) ? current : shortest;
        }, null);
        if (!route?.geometry?.coordinates?.length) { throw new Error('Route geometry missing'); }
        return route.geometry.coordinates.map(function (point) { return { lat: point[1], lng: point[0] }; });
    };

    const resetLeafletRoute = function () {
        if (!leafletMap) { return; }
        leafletMarkers.forEach(marker => leafletMap.removeLayer(marker));
        leafletMarkers = [];
        if (leafletPolyline) { leafletMap.removeLayer(leafletPolyline); leafletPolyline = null; }
    };

    const escapeHtml = function (value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const createMarkerPopupHtml = function (title, subtitle, lines) {
        return `
            <div style="min-width:220px;">
                <div style="font-weight:800;color:#0f172a;font-size:1rem;margin-bottom:4px;">${escapeHtml(title)}</div>
                ${subtitle ? `<div style="color:#64748b;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">${escapeHtml(subtitle)}</div>` : ''}
                ${lines.filter(Boolean).map(line => `<div style="color:#475569;font-size:.9rem;line-height:1.5;">${escapeHtml(line)}</div>`).join('')}
            </div>
        `;
    };

    const drawLeafletRoute = async function (branch, customer) {
        if (!leafletMap) {
            leafletMap = L.map('orderRouteMapCanvas', { zoomControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(leafletMap);
        }
        resetLeafletRoute();
        const branchMarker = L.marker([branch.lat, branch.lng]).addTo(leafletMap)
            .bindPopup(createMarkerPopupHtml(branch.name, 'Branch', [branch.address, `${branch.lat.toFixed(6)}, ${branch.lng.toFixed(6)}`]));
        const customerMarker = L.marker([customer.lat, customer.lng]).addTo(leafletMap)
            .bindPopup(createMarkerPopupHtml(customer.name, 'Customer', [customer.address, `${customer.lat.toFixed(6)}, ${customer.lng.toFixed(6)}`]));
        leafletMarkers.push(branchMarker, customerMarker);
        try {
            const points = await fetchRoadRoute(branch, customer);
            leafletPolyline = L.polyline(points.map(point => [point.lat, point.lng]), { color: '#2563eb', weight: 4, opacity: 0.9 }).addTo(leafletMap);
            leafletMap.fitBounds(leafletPolyline.getBounds(), { padding: [40, 40] });
        } catch (error) {
            leafletPolyline = L.polyline([[branch.lat, branch.lng], [customer.lat, customer.lng]], { color: '#2563eb', weight: 4, opacity: 0.85, dashArray: '8 10' }).addTo(leafletMap);
            leafletMap.fitBounds(leafletPolyline.getBounds(), { padding: [40, 40] });
        }
        setTimeout(() => leafletMap.invalidateSize(), 180);
    };

    const fetchShortestRoute = async function (origin, destination) {
        const url = `https://router.project-osrm.org/route/v1/driving/${origin.lng},${origin.lat};${destination.lng},${destination.lat}?overview=full&geometries=geojson&alternatives=true&steps=false`;
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) { throw new Error('Failed to fetch route'); }
        const payload = await response.json();
        const route = (payload?.routes || []).reduce(function (shortest, current) {
            if (!shortest) { return current; }
            return Number(current?.distance || 0) < Number(shortest?.distance || 0) ? current : shortest;
        }, null);
        if (!route?.geometry?.coordinates?.length) { throw new Error('Route geometry missing'); }
        return {
            distanceKm: Number(route.distance || 0) / 1000,
            points: route.geometry.coordinates.map(function (point) {
                return { lat: point[1], lng: point[0] };
            }),
        };
    };

    const setPlannerStatus = function (message, isError = false) {
        plannerStatusMessage.textContent = message;
        plannerStatusMessage.classList.toggle('is-error', isError);
    };

    const setSingleRouteStatus = function (message, isError = false) {
        singleRouteStatusMessage.textContent = message;
        singleRouteStatusMessage.classList.toggle('is-error', isError);
    };

    const getCurrentStatusFilter = function () {
        return $('.js-order-filter-card.is-active').data('status') || 'all';
    };

    const clearPlannerLeaflet = function () {
        if (!plannerLeafletMap) { return; }
        plannerLeafletLayers.forEach(layer => plannerLeafletMap.removeLayer(layer));
        plannerLeafletLayers = [];
    };

    const clearPlannerGoogle = function () {
        plannerGoogleObjects.forEach(object => object.setMap && object.setMap(null));
        plannerGoogleObjects = [];
    };

    const ensurePlannerLeaflet = function () {
        if (!plannerLeafletMap) {
            plannerLeafletMap = L.map('routePlannerMapCanvas', { zoomControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(plannerLeafletMap);
        }
        return plannerLeafletMap;
    };

    const ensurePlannerGoogle = async function () {
        if (!canUseGoogleMaps) { return null; }
        let attempts = 0;
        while (!(window.google && window.google.maps) && attempts < 40) {
            await new Promise(resolve => setTimeout(resolve, 250));
            attempts += 1;
        }
        if (!(window.google && window.google.maps)) {
            return null;
        }
        if (!plannerGoogleMap) {
            plannerGoogleMap = new google.maps.Map(document.getElementById('routePlannerMapCanvas'), {
                center: { lat: 33.6844, lng: 73.0479 },
                zoom: 12,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });
        }
        return plannerGoogleMap;
    };

    const ensureOrderRouteGoogle = async function () {
        if (!canUseGoogleMaps) { return null; }
        let attempts = 0;
        while (!(window.google && window.google.maps) && attempts < 40) {
            await new Promise(resolve => setTimeout(resolve, 250));
            attempts += 1;
        }
        if (!(window.google && window.google.maps)) {
            return null;
        }
        if (!orderRouteGoogleMap) {
            orderRouteGoogleMap = new google.maps.Map(document.getElementById('orderRouteMapCanvas'), {
                center: { lat: 33.6844, lng: 73.0479 },
                zoom: 12,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });
        }
        return orderRouteGoogleMap;
    };

    const renderPlannerOrderList = function (orders) {
        if (!orders.length) {
            plannerOrderList.innerHTML = '<div class="planner-empty">No mappable orders were found for the current branch assignment and filter.</div>';
            return;
        }

        plannerOrderList.innerHTML = orders.map(order => `
            <div class="planner-order">
                <strong>${order.order_number}</strong>
                <span>${order.customer_name}</span>
                <span>${order.branch_name} -> ${order.customer_address}</span>
                <small>${statusLabels[order.status] || order.status}</small>
            </div>
        `).join('');
    };

    const clearOrderRouteGoogle = function () {
        orderRouteGoogleObjects.forEach(object => object.setMap && object.setMap(null));
        orderRouteGoogleObjects = [];
    };

    const drawOrderRouteGoogle = async function (branch, customer) {
        const map = await ensureOrderRouteGoogle();
        if (!map) {
            await drawLeafletRoute(branch, customer);
            return;
        }

        clearOrderRouteGoogle();
        const bounds = new google.maps.LatLngBounds();
        const infoWindow = new google.maps.InfoWindow();

        const branchMarker = new google.maps.Marker({
            map,
            position: { lat: branch.lat, lng: branch.lng },
            title: branch.name
        });
        branchMarker.addListener('click', function () {
            infoWindow.setContent(createMarkerPopupHtml(branch.name, 'Branch', [branch.address, `${branch.lat.toFixed(6)}, ${branch.lng.toFixed(6)}`]));
            infoWindow.open({ anchor: branchMarker, map });
        });

        const customerMarker = new google.maps.Marker({
            map,
            position: { lat: customer.lat, lng: customer.lng },
            title: customer.name
        });
        customerMarker.addListener('click', function () {
            infoWindow.setContent(createMarkerPopupHtml(customer.name, 'Customer', [customer.address, `${customer.lat.toFixed(6)}, ${customer.lng.toFixed(6)}`]));
            infoWindow.open({ anchor: customerMarker, map });
        });

        orderRouteGoogleObjects.push(branchMarker, customerMarker);

        try {
            const route = await fetchShortestRoute(branch, customer);
            const polyline = new google.maps.Polyline({
                map,
                path: route.points,
                geodesic: true,
                strokeColor: '#2563eb',
                strokeOpacity: 0.9,
                strokeWeight: 4,
            });
            orderRouteGoogleObjects.push(polyline);
            route.points.forEach(point => bounds.extend(point));
        } catch (error) {
            const polyline = new google.maps.Polyline({
                map,
                path: [{ lat: branch.lat, lng: branch.lng }, { lat: customer.lat, lng: customer.lng }],
                geodesic: true,
                strokeColor: '#ef4444',
                strokeOpacity: 0.55,
                strokeWeight: 3,
            });
            orderRouteGoogleObjects.push(polyline);
            bounds.extend({ lat: branch.lat, lng: branch.lng });
            bounds.extend({ lat: customer.lat, lng: customer.lng });
        }

        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
        }
    };

    const drawPlannerRoutesLeaflet = async function (orders, originMode) {
        const map = ensurePlannerLeaflet();
        clearPlannerLeaflet();
        const bounds = [];
        let totalDistanceKm = 0;

        for (const order of orders) {
            const origin = originMode === 'current' && plannerCurrentLocation
                ? plannerCurrentLocation
                : { lat: order.branch_latitude, lng: order.branch_longitude };
            const destination = { lat: order.customer_latitude, lng: order.customer_longitude };

            try {
                const route = await fetchShortestRoute(origin, destination);
                totalDistanceKm += route.distanceKm;
                const line = L.polyline(route.points.map(point => [point.lat, point.lng]), {
                    color: originMode === 'current' ? '#16a34a' : '#2563eb',
                    weight: 4,
                    opacity: 0.85,
                }).addTo(map);
                plannerLeafletLayers.push(line);
                route.points.forEach(point => bounds.push([point.lat, point.lng]));
            } catch (error) {
                const fallback = L.polyline([[origin.lat, origin.lng], [destination.lat, destination.lng]], {
                    color: '#ef4444',
                    weight: 3,
                    opacity: 0.55,
                    dashArray: '6 8'
                }).addTo(map);
                plannerLeafletLayers.push(fallback);
                bounds.push([origin.lat, origin.lng], [destination.lat, destination.lng]);
            }

            const originMarker = L.marker([origin.lat, origin.lng]).addTo(map);
            originMarker.bindPopup(createMarkerPopupHtml(
                originMode === 'current' ? 'Current Location' : order.branch_name,
                originMode === 'current' ? 'Start Point' : 'Branch',
                [
                    originMode === 'current' ? 'Live location selected by the user' : order.branch_address,
                    `${origin.lat.toFixed(6)}, ${origin.lng.toFixed(6)}`
                ]
            ));
            const customerMarker = L.marker([destination.lat, destination.lng]).addTo(map)
                .bindPopup(createMarkerPopupHtml(order.customer_name, order.order_number, [order.customer_address, `${destination.lat.toFixed(6)}, ${destination.lng.toFixed(6)}`]));
            plannerLeafletLayers.push(originMarker, customerMarker);
        }

        plannerOrderCount.textContent = String(orders.length);
        plannerDistanceTotal.textContent = `${totalDistanceKm.toFixed(2)} km`;
        if (bounds.length) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
        setTimeout(() => map.invalidateSize(), 180);
    };

    const drawPlannerRoutesGoogle = async function (orders, originMode) {
        const map = await ensurePlannerGoogle();
        if (!map) {
            setPlannerStatus('Google Maps could not be loaded, so the planner stayed on the fallback map mode.', true);
            await drawPlannerRoutesLeaflet(orders, originMode);
            return;
        }

        clearPlannerGoogle();
        const bounds = new google.maps.LatLngBounds();
        let totalDistanceKm = 0;

        for (const order of orders) {
            const origin = originMode === 'current' && plannerCurrentLocation
                ? plannerCurrentLocation
                : { lat: order.branch_latitude, lng: order.branch_longitude };
            const destination = { lat: order.customer_latitude, lng: order.customer_longitude };

            try {
                const route = await fetchShortestRoute(origin, destination);
                totalDistanceKm += route.distanceKm;
                const polyline = new google.maps.Polyline({
                    map,
                    path: route.points,
                    geodesic: true,
                    strokeColor: originMode === 'current' ? '#16a34a' : '#2563eb',
                    strokeOpacity: 0.85,
                    strokeWeight: 4,
                });
                plannerGoogleObjects.push(polyline);
                route.points.forEach(point => bounds.extend(point));
            } catch (error) {
                const fallbackPolyline = new google.maps.Polyline({
                    map,
                    path: [origin, destination],
                    geodesic: true,
                    strokeColor: '#ef4444',
                    strokeOpacity: 0.55,
                    strokeWeight: 3,
                });
                plannerGoogleObjects.push(fallbackPolyline);
                bounds.extend(origin);
                bounds.extend(destination);
            }

            const infoWindow = new google.maps.InfoWindow();
            const originMarker = new google.maps.Marker({ map, position: origin, title: originMode === 'current' ? 'Current Location' : order.branch_name });
            originMarker.addListener('click', function () {
                infoWindow.setContent(createMarkerPopupHtml(
                    originMode === 'current' ? 'Current Location' : order.branch_name,
                    originMode === 'current' ? 'Start Point' : 'Branch',
                    [
                        originMode === 'current' ? 'Live location selected by the user' : order.branch_address,
                        `${origin.lat.toFixed(6)}, ${origin.lng.toFixed(6)}`
                    ]
                ));
                infoWindow.open({ anchor: originMarker, map });
            });
            const customerMarker = new google.maps.Marker({ map, position: destination, title: order.customer_name });
            customerMarker.addListener('click', function () {
                infoWindow.setContent(createMarkerPopupHtml(order.customer_name, order.order_number, [order.customer_address, `${destination.lat.toFixed(6)}, ${destination.lng.toFixed(6)}`]));
                infoWindow.open({ anchor: customerMarker, map });
            });
            plannerGoogleObjects.push(originMarker, customerMarker);
        }

        plannerOrderCount.textContent = String(orders.length);
        plannerDistanceTotal.textContent = `${totalDistanceKm.toFixed(2)} km`;
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
        }
    };

    const loadPlannerData = async function () {
        setPlannerStatus(plannerOriginMode === 'current'
            ? 'Trying to use your live location as the starting point for all assigned orders.'
            : 'Routes are being calculated from each order branch.');

        const response = await fetch(`{{ route('frontend.staff.route-planner-data') }}?status_filter=${encodeURIComponent(getCurrentStatusFilter())}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Unable to load route planner data.');
        }

        const payload = await response.json();
        return Array.isArray(payload.orders) ? payload.orders : [];
    };

    const resolveCurrentLocation = async function () {
        if (plannerOriginMode !== 'current') {
            return null;
        }

        if (plannerCurrentLocation) {
            return plannerCurrentLocation;
        }

        if (!navigator.geolocation) {
            throw new Error('Geolocation is not supported on this device.');
        }

        plannerCurrentLocation = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => resolve({ lat: position.coords.latitude, lng: position.coords.longitude }),
                () => reject(new Error('We could not read your current location.')),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });

        return plannerCurrentLocation;
    };

    const resolveSingleRouteCurrentLocation = async function () {
        if (singleRouteOriginMode !== 'current') {
            return null;
        }

        if (singleRouteCurrentLocation) {
            return singleRouteCurrentLocation;
        }

        if (!navigator.geolocation) {
            throw new Error('Geolocation is not supported on this device.');
        }

        singleRouteCurrentLocation = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => resolve({ lat: position.coords.latitude, lng: position.coords.longitude }),
                () => reject(new Error('We could not read your current location.')),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });

        return singleRouteCurrentLocation;
    };

    const renderSingleRoute = async function () {
        if (!activeSingleRoute) {
            return;
        }

        try {
            if (singleRouteOriginMode === 'current') {
                const currentLocation = await resolveSingleRouteCurrentLocation();
                const origin = {
                    ...currentLocation,
                    name: 'Current Location',
                    address: 'Live location selected by the user'
                };
                setSingleRouteStatus('This route now starts from your current location.');
                if (canUseGoogleMaps) {
                    await drawOrderRouteGoogle(origin, activeSingleRoute.customer);
                } else {
                    await drawLeafletRoute(origin, activeSingleRoute.customer);
                }
                return;
            }

            setSingleRouteStatus('This route currently starts from the assigned branch.');
            if (canUseGoogleMaps) {
                await drawOrderRouteGoogle(activeSingleRoute.branch, activeSingleRoute.customer);
            } else {
                await drawLeafletRoute(activeSingleRoute.branch, activeSingleRoute.customer);
            }
        } catch (error) {
            setSingleRouteStatus(error.message, true);
        }
    };

    const renderRoutePlanner = async function () {
        try {
            if (plannerOriginMode === 'current') {
                await resolveCurrentLocation();
            }

            const orders = await loadPlannerData();
            renderPlannerOrderList(orders);

            if (!orders.length) {
                plannerOrderCount.textContent = '0';
                plannerDistanceTotal.textContent = '0.00 km';
                setPlannerStatus('No mappable orders were found for the selected filter.');
                return;
            }

            if (canUseGoogleMaps) {
                await drawPlannerRoutesGoogle(orders, plannerOriginMode);
            } else {
                await drawPlannerRoutesLeaflet(orders, plannerOriginMode);
            }

            setPlannerStatus(plannerOriginMode === 'current'
                ? 'Shortest routes are now using your current location as the origin.'
                : 'Shortest routes are now using each assigned branch as the origin.');
        } catch (error) {
            plannerOrderList.innerHTML = `<div class="planner-empty">${error.message}</div>`;
            plannerOrderCount.textContent = '0';
            plannerDistanceTotal.textContent = '0.00 km';
            setPlannerStatus(error.message, true);
        }
    };

    $(document).on('click', '.js-show-order-route', function () {
        const button = this;
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
        if (googleUrl) { routeGoogleMapsButton.href = googleUrl; routeGoogleMapsButton.classList.remove('d-none'); } else { routeGoogleMapsButton.classList.add('d-none'); }
        activeSingleRoute = { branch, customer };
        singleRouteOriginMode = 'branch';
        singleOriginButtons.forEach(item => item.classList.toggle('is-active', item.dataset.singleOriginMode === 'branch'));
        setSingleRouteStatus('This route currently starts from the assigned branch.');
        routeModal.show();
        setTimeout(function () { renderSingleRoute(); }, 220);
    });

    $(document).on('click', '.js-open-order-invoice', function () {
        const button = this;
        const invoiceUrl = button.dataset.url || '';
        if (!invoiceUrl || !invoiceFrame) { return; }
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
            url: `{{ url('/staff/orders') }}/${orderId}/available-drivers`,
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
            url: `{{ url('/staff/orders') }}/${orderId}/available-branches`,
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
        if (!updateUrl || !nextValue) { return; }
        const defaultText = orderStatusSaveButton.textContent;
        orderStatusSaveButton.disabled = true;
        orderStatusSaveButton.textContent = 'Saving...';
        $.ajax({
            url: updateUrl,
            type: 'PATCH',
            data: {
                _token: @json(csrf_token()),
                order_status: nextValue,
                confirm_payment_received: orderStatusPaymentConfirm?.checked ? 1 : 0
            },
            success: function () {
                orderStatusSaveButton.disabled = false;
                orderStatusSaveButton.textContent = defaultText;
                statusModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                orderStatusSaveButton.disabled = false;
                orderStatusSaveButton.textContent = defaultText;
                Swal.fire({
                    icon: 'error',
                    title: @json(__('error')),
                    text: xhr.responseJSON?.message || 'Unable to update order status right now.',
                    confirmButtonText: @json(__('ok'))
                });
            }
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
        if (!orderId || !driverId) { return; }

        const defaultText = assignDriverSaveButton.textContent;
        assignDriverSaveButton.disabled = true;
        assignDriverSaveButton.textContent = 'Assigning...';

        $.ajax({
            url: `{{ url('/staff/orders') }}/${orderId}/assign-driver`,
            type: 'PATCH',
            data: { _token: @json(csrf_token()), driver_id: driverId },
            success: function () {
                assignDriverSaveButton.disabled = false;
                assignDriverSaveButton.textContent = defaultText;
                assignDriverModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                assignDriverSaveButton.disabled = false;
                assignDriverSaveButton.textContent = defaultText;
                alert(xhr.responseJSON?.message || 'Unable to assign driver right now.');
            }
        });
    });

    changeBranchForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const orderId = changeBranchOrderId.value;
        const organizationId = changeBranchSelect.value;
        if (!orderId || !organizationId) { return; }

        const defaultText = changeBranchSaveButton.textContent;
        changeBranchSaveButton.disabled = true;
        changeBranchSaveButton.textContent = 'Saving...';

        $.ajax({
            url: `{{ url('/staff/orders') }}/${orderId}/change-branch`,
            type: 'PATCH',
            data: { _token: @json(csrf_token()), organization_id: organizationId },
            success: function () {
                changeBranchSaveButton.disabled = false;
                changeBranchSaveButton.textContent = defaultText;
                changeBranchModal.hide();
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                changeBranchSaveButton.disabled = false;
                changeBranchSaveButton.textContent = defaultText;
                alert(xhr.responseJSON?.message || 'Unable to change the branch right now.');
            }
        });
    });

    routeModalElement.addEventListener('shown.bs.modal', function () { if (leafletMap) { leafletMap.invalidateSize(); } });
    invoiceModalElement.addEventListener('hidden.bs.modal', function () { if (invoiceFrame) { invoiceFrame.src = 'about:blank'; } });

    openRoutePlannerButton?.addEventListener('click', async function () {
        plannerModal.show();
        await renderRoutePlanner();
    });

    plannerOriginButtons.forEach(button => {
        button.addEventListener('click', async function () {
            plannerOriginButtons.forEach(item => item.classList.remove('is-active'));
            this.classList.add('is-active');
            plannerOriginMode = this.dataset.originMode || 'branch';
            if (plannerModalElement.classList.contains('show')) {
                await renderRoutePlanner();
            }
        });
    });

    singleOriginButtons.forEach(button => {
        button.addEventListener('click', async function () {
            singleOriginButtons.forEach(item => item.classList.remove('is-active'));
            this.classList.add('is-active');
            singleRouteOriginMode = this.dataset.singleOriginMode || 'branch';
            if (routeModalElement.classList.contains('show')) {
                await renderSingleRoute();
            }
        });
    });

    plannerModalElement.addEventListener('shown.bs.modal', function () {
        if (plannerLeafletMap) {
            plannerLeafletMap.invalidateSize();
        }
        if (plannerGoogleMap && window.google?.maps) {
            google.maps.event.trigger(plannerGoogleMap, 'resize');
        }
    });

    ['pointerdown', 'keydown'].forEach(eventName => {
        window.addEventListener(eventName, unlockNotificationAudio, { once: true, passive: true });
    });

    window.__staffLatestNotificationId = @json($portalLatestNotificationId);
    window.setInterval(pollStaffNotifications, 15000);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initFrontendStaffDashboard, { once: true });
} else {
    window.initFrontendStaffDashboard();
}
</script>
@endsection
