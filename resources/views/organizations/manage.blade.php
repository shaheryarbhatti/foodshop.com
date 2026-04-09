<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('manage_organizations') }}</title>

    @extends('layouts.app')
    @section('content')
    @php
        $user = auth()->user();
        $legacyBase = 'manage-' . \Illuminate\Support\Str::singular('organizations');
        $canOrganizationAdd = $user && $user->canAny(['organizations.add', $legacyBase . '.add']);
        $mapProvider = \App\Models\Setting::get('map_provider', 'leaflet');
        $googleMapsApiKey = \App\Models\Setting::get('google_maps_api_key', '');
        $canUseGoogleMaps = $mapProvider === 'google' && filled($googleMapsApiKey);
    @endphp

    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card" style="margin-top: 20px;">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ __('manage_organizations') }}</h4>
                            @if($canOrganizationAdd)
                                <a href="{{ route('organizations.add') }}" class="btn btn-primary">
                                    <i class="fa fa-plus me-2"></i> {{ __('add_new_organization') }}
                                </a>
                            @endif
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

                            <div class="table-responsive custom-scrollbar mb-4">
                                <table id="organizationsTable" class="display table table-hover table-bordered" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('organization_name') }}</th>
                                            <th>{{ __('location_name') }}</th>
                                            <th>{{ __('latitude') }}</th>
                                            <th>{{ __('longitude') }}</th>
                                            <th>{{ __('map') }}</th>
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

    <div class="modal fade" id="branchMapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">{{ __('branch_map_preview') }}</h5>
                        <small class="text-muted" id="branchMapMeta">-</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('close') }}"></button>
                </div>
                <div class="modal-body">
                    <div id="branchMapCanvas" style="height: 420px; border-radius: 16px; overflow: hidden;"></div>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        @unless($canUseGoogleMaps)
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        @endunless
        <style>
            div.dataTables_wrapper div.dataTables_filter {
                position: absolute;
                right: 53px;
                top: 0px;
            }

            .dataTables_paginate .pagination {
                margin: 0 auto !important;
                justify-content: center;
                gap: 0.25rem;
                padding: 0.5rem 0;
            }

            .dataTables_paginate .page-item .page-link {
                border-radius: 50% !important;
                width: 38px;
                height: 38px;
                line-height: 38px;
                text-align: center;
                padding: 0;
                margin: 0 2px;
                border: 1px solid #dee2e6;
                color: #495057;
                background-color: #fff;
                transition: all 0.2s ease;
                font-weight: 500;
            }

            .dataTables_paginate .page-item .page-link:hover {
                background-color: var(--theme-default, #7367f0) !important;
                color: white !important;
                border-color: var(--theme-default, #7367f0) !important;
                transform: scale(1.08);
            }

            .dataTables_paginate .page-item.active .page-link {
                background-color: var(--theme-default, #7367f0) !important;
                border-color: var(--theme-default, #7367f0) !important;
                color: white !important;
                box-shadow: 0 2px 6px rgba(115, 103, 240, 0.3);
                font-weight: bold;
            }

            .dataTables_paginate .page-item.disabled .page-link {
                color: #adb5bd !important;
                background-color: #f8f9fa !important;
                border-color: #dee2e6 !important;
                cursor: not-allowed;
            }

            .dataTables_paginate .page-item:first-child .page-link,
            .dataTables_paginate .page-item:last-child .page-link {
                border-radius: 0.375rem !important;
                width: auto;
                padding: 0 1rem;
            }

            .dataTables_wrapper .dataTables_filter {
                margin-bottom: 2.5rem !important;
            }

            .dataTables_wrapper .dataTables_paginate {
                margin-top: 1.5rem !important;
            }

            .branch-map-btn[disabled] {
                opacity: 0.6;
                cursor: not-allowed;
            }

            .leaflet-popup-content-wrapper {
                border-radius: 14px;
            }

            #branchMapCanvas iframe,
            #branchMapCanvas .gm-style {
                border-radius: 16px;
            }

            @media (max-width: 576px) {
                .dataTables_paginate .page-link {
                    width: 32px;
                    height: 32px;
                    line-height: 32px;
                    font-size: 0.875rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
        @unless($canUseGoogleMaps)
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @endunless
        <script>
            window.initBranchManageMapProvider = function() {
                if (@json($canUseGoogleMaps) && !(window.google && window.google.maps)) {
                    return;
                }
                if (window.__branchManageMapProviderInitialized) {
                    return;
                }
                window.__branchManageMapProviderInitialized = true;

                $('#organizationsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('organizations.manage') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'location_name',
                            name: 'location_name',
                            searchable: false
                        },
                        {
                            data: 'latitude_value',
                            name: 'latitude'
                        },
                        {
                            data: 'longitude_value',
                            name: 'longitude'
                        },
                        {
                            data: 'map_button',
                            name: 'map_button',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'desc']],
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    },
                    responsive: true,
                    dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip',
                });

                const mapModalElement = document.getElementById('branchMapModal');
                const mapMeta = document.getElementById('branchMapMeta');
                const mapModal = new bootstrap.Modal(mapModalElement);
                let branchMap;
                let branchMarker;
                let googleMap;
                let googleMarker;

                $(document).on('click', '.js-show-branch-map', function() {
                    const button = this;
                    const name = button.dataset.name || '-';
                    const location = button.dataset.location || '-';
                    const address = button.dataset.address || '-';
                    const latitude = parseFloat(button.dataset.latitude || 0);
                    const longitude = parseFloat(button.dataset.longitude || 0);
                    const mapCenter = { lat: latitude, lng: longitude };

                    mapMeta.textContent = `${name} | ${location} | ${address}`;
                    mapModal.show();

                    setTimeout(() => {
                        if (@json($canUseGoogleMaps) && window.google?.maps) {
                            if (!googleMap) {
                                googleMap = new google.maps.Map(document.getElementById('branchMapCanvas'), {
                                    center: mapCenter,
                                    zoom: 15,
                                    mapTypeControl: false,
                                    streetViewControl: false,
                                    fullscreenControl: true
                                });
                            } else {
                                googleMap.setCenter(mapCenter);
                                googleMap.setZoom(15);
                            }

                            if (googleMarker) {
                                googleMarker.setMap(null);
                            }

                            googleMarker = new google.maps.Marker({
                                position: mapCenter,
                                map: googleMap,
                                title: name
                            });

                            return;
                        }

                        if (!branchMap) {
                            branchMap = L.map('branchMapCanvas', {
                                zoomControl: true
                            });

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(branchMap);
                        }

                        if (branchMarker) {
                            branchMap.removeLayer(branchMarker);
                        }

                        branchMarker = L.marker([latitude, longitude]).addTo(branchMap);
                        branchMarker.bindPopup(`
                            <div style="min-width: 180px;">
                                <strong>${name}</strong><br>
                                <span>${location}</span><br>
                                <small>${address}</small>
                            </div>
                        `).openPopup();

                        branchMap.setView([latitude, longitude], 15);
                        branchMap.invalidateSize();
                    }, 250);
                });

                mapModalElement.addEventListener('shown.bs.modal', function() {
                    if (branchMap) {
                        branchMap.invalidateSize();
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', window.initBranchManageMapProvider, { once: true });
            } else {
                window.initBranchManageMapProvider();
            }
        </script>
        @if($canUseGoogleMaps)
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsApiKey) }}&callback=initBranchManageMapProvider"></script>
        @endif
    @endpush
