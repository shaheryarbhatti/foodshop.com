<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('edit_organization') }}</title>

    @extends('layouts.app')
    @section('content')
    @php
        $mapProvider = \App\Models\Setting::get('map_provider', 'leaflet');
        $googleMapsApiKey = \App\Models\Setting::get('google_maps_api_key', '');
        $canUseGoogleMaps = $mapProvider === 'google' && filled($googleMapsApiKey);
    @endphp
    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="margin-top: 20px;">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ __('edit_organization') }}</h4>
                            <a href="{{ route('organizations.manage') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left me-2"></i> {{ __('back_to_list') }}
                            </a>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Whoops!</strong> There were some problems with your input.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('organizations.update', $organization->id) }}" class="theme-form">
                                @csrf
                                @method('PUT')

                                <div class="row g-4">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="name">{{ __('organization_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $organization->name) }}" placeholder="{{ __('organization_name') }}" required autofocus>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="location_id">{{ __('location_name') }} <span class="text-danger">*</span></label>
                                            <select name="location_id" id="location_id" class="form-select searchable-select @error('location_id') is-invalid @enderror" required>
                                                <option value="">{{ __('location_name') }}</option>
                                                @foreach($locations as $location)
                                                    <option value="{{ $location->id }}" {{ old('location_id', $organization->location_id) == $location->id ? 'selected' : '' }}>
                                                        {{ $location->name }} ({{ optional($location->region)->name }}{{ optional(optional($location->region)->country)->name ? ' - ' . optional(optional($location->region)->country)->name : '' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('location_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3 position-relative">
                                            <label class="col-form-label" for="address_search">{{ __('organization_address_search') }}</label>
                                            <input type="text" name="address" id="address_search" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $organization->address) }}" placeholder="{{ __('organization_address_search_placeholder') }}" autocomplete="off">
                                            <div class="list-group shadow-sm position-absolute w-100 d-none" id="addressSearchResults" style="z-index: 1050; top: 100%; left: 0; background-color:white !important"></div>
                                            <div class="form-text">{{ __('organization_address_search_note') }}</div>
                                            @error('address')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="branch-map-card">
                                            <div class="branch-map-card-head">
                                                <div>
                                                    <h6 class="mb-1">{{ __('organization_map_preview') }}</h6>
                                                    <small class="text-muted">{{ __('organization_map_drag_note') }}</small>
                                                </div>
                                            </div>
                                            <div id="branchAddressMap" class="branch-address-map"></div>
                                            <div class="branch-map-loader" id="branchMapLoader">
                                                <i class="fa fa-spinner fa-spin"></i>
                                                <span>{{ __('organization_map_updating_address') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="latitude">{{ __('latitude') }}</label>
                                            <input type="text" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $organization->latitude) }}" placeholder="e.g. 24.860734">
                                            @error('latitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="longitude">{{ __('longitude') }}</label>
                                            <input type="text" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $organization->longitude) }}" placeholder="e.g. 67.001137">
                                            @error('longitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 d-flex align-items-end">
                                        <div class="mb-3 w-100">
                                            <button type="button" class="btn btn-outline-info w-100" id="fetchCoordinatesBtn">
                                                <i class="fa fa-location-crosshairs me-2"></i>{{ __('get_coordinates_from_address') }}
                                            </button>
                                            <div class="form-text" id="coordinateHelperText">{{ __('organization_map_geocode_note') }}</div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="status">{{ __('status') }} <span class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                                <option value="1" {{ old('status', $organization->status ? '1' : '0') == '1' ? 'selected' : '' }}>{{ __('active') }}</option>
                                                <option value="0" {{ old('status', $organization->status ? '1' : '0') == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="fa fa-refresh me-2"></i> {{ __('update_organization') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
        @if(! $canUseGoogleMaps)
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        @endif
        <style>
            .branch-address-search-wrap {
                position: relative;
            }

            .branch-address-results {
                position: relative;
                margin-top: 8px;
                max-height: 260px;
                overflow-y: auto;
                border: 1px solid #d8e2ee !important;
                border-radius: 16px;
                background: #ffffff !important;
                box-shadow: 0 18px 40px rgba(16, 24, 40, 0.12);
                isolation: isolate;
            }

            .branch-address-results .list-group-item {
                border: 0;
                border-bottom: 1px solid #edf2f7;
                padding: 14px 16px;
                background: #ffffff !important;
                color: #000000 !important;
                transition: background-color 0.18s ease;
                text-align: left;
                opacity: 1 !important;
            }

            .branch-address-results .list-group-item:last-child {
                border-bottom: 0;
            }

            .branch-address-results .list-group-item:hover,
            .branch-address-results .list-group-item:focus {
                background: #f8fbff;
            }

            .branch-address-results-title {
                display: block;
                margin-bottom: 4px;
                font-size: 1rem;
                font-weight: 700;
                color: #000000;
                line-height: 1.35;
            }

            .branch-address-results-label {
                display: block;
                font-size: 0.9rem;
                color: #000000;
                line-height: 1.45;
                white-space: normal;
                word-break: break-word;
            }

            .branch-map-card {
                position: relative;
                border: 1px solid #d8e2ee;
                border-radius: 16px;
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 18px 40px rgba(16, 24, 40, 0.08);
            }

            .branch-map-card-head {
                padding: 14px 16px;
                border-bottom: 1px solid #edf2f7;
                background: #f8fbff;
            }

            .branch-address-map {
                height: 320px;
                width: 100%;
                background: #f8fafc;
            }

            .branch-map-loader {
                position: absolute;
                right: 16px;
                bottom: 16px;
                display: none;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                border-radius: 999px;
                background: rgba(15, 23, 42, 0.88);
                color: #fff;
                font-size: 0.84rem;
                font-weight: 700;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.24);
                z-index: 1000;
            }

            .branch-map-loader.is-visible {
                display: inline-flex;
            }

            .pac-container {
                z-index: 20000 !important;
                background: #fff !important;
                border: 1px solid rgba(15, 23, 42, 0.12) !important;
                border-radius: 14px !important;
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12) !important;
                overflow: hidden;
            }

            .pac-container .pac-item {
                padding: 10px 14px;
                color: #111827;
                background: #fff;
            }

            .pac-container .pac-item-query {
                color: #111827;
                font-size: 0.95rem;
                font-weight: 700;
            }
        </style>
    @endpush

    @push('scripts')
        @if(! $canUseGoogleMaps)
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @endif
        <script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
        <script>
            window.initBranchAddressProvider = function () {
                if (@json($canUseGoogleMaps) && !(window.google && window.google.maps)) {
                    return;
                }
                if (window.__branchAddressProviderInitialized) {
                    return;
                }
                window.__branchAddressProviderInitialized = true;

                $('.searchable-select').select2({
                    width: '100%',
                    placeholder: @json(__('location_name')),
                    allowClear: true
                });

                const fetchButton = document.getElementById('fetchCoordinatesBtn');
                const searchField = document.getElementById('address_search');
                const resultsBox = document.getElementById('addressSearchResults');
                const searchWrap = searchField ? searchField.closest('.branch-address-search-wrap') : null;
                const addressField = document.getElementById('address_search');
                const latitudeField = document.getElementById('latitude');
                const longitudeField = document.getElementById('longitude');
                const helperText = document.getElementById('coordinateHelperText');
                const locationField = document.getElementById('location_id');
                const mapCanvas = document.getElementById('branchAddressMap');
                const mapLoader = document.getElementById('branchMapLoader');
                const addressSearchEndpoint = @json(route('organizations.address-search'));
                const geocodeEndpoint = @json(route('organizations.geocode-address'));
                const reverseGeocodeEndpoint = @json(route('organizations.reverse-geocode-address'));
                let searchTimeout;
                let selectedGooglePlace = null;
                let selectedFreePlace = null;
                let mapInstance = null;
                let mapMarker = null;
                let mapGeocoder = null;
                const setMapLoader = (visible) => {
                    if (!mapLoader) {
                        return;
                    }

                    mapLoader.classList.toggle('is-visible', visible);
                };

                const hideResults = () => {
                    resultsBox.innerHTML = '';
                    resultsBox.classList.add('d-none');
                };

                const escapeHtml = (value) => $('<div>').text(value || '').html();

                const renderResults = (items) => {
                    if (!items.length) {
                        resultsBox.innerHTML = `<div class="list-group-item small text-muted" style="background-color:white !important"">${@json(__('organization_address_search_empty'))}</div>`;
                        resultsBox.classList.remove('d-none');
                        return;
                    }

                    resultsBox.innerHTML = items.map((item) => `
                        <button type="button"
                                class="list-group-item list-group-item-action js-address-result"
                                data-address="${escapeHtml(item.label)}"
                                data-latitude="${escapeHtml(item.latitude)}"
                                data-longitude="${escapeHtml(item.longitude)}">
                            <span class="branch-address-results-title">${escapeHtml(item.title)}</span>
                            <span class="branch-address-results-label">${escapeHtml(item.label)}</span>
                        </button>
                    `).join('');

                    resultsBox.classList.remove('d-none');
                };

                const buildLocationText = () => {
                    return locationField && locationField.selectedIndex >= 0
                        ? locationField.options[locationField.selectedIndex].text.trim()
                        : '';
                };

                const setCoordinates = (latitude, longitude, updateAddress = false) => {
                    latitudeField.value = latitude ? String(latitude) : '';
                    longitudeField.value = longitude ? String(longitude) : '';
                    updateMap(latitude, longitude, updateAddress);
                };

                const reverseGeocode = async (latitude, longitude) => {
                    if (!latitude || !longitude) {
                        return;
                    }

                    setMapLoader(true);

                    if (@json($canUseGoogleMaps) && window.google?.maps?.Geocoder) {
                        mapGeocoder = mapGeocoder || new google.maps.Geocoder();
                        mapGeocoder.geocode({ location: { lat: Number(latitude), lng: Number(longitude) } }, (results, status) => {
                            if (status === 'OK' && Array.isArray(results) && results.length) {
                                addressField.value = results[0].formatted_address || addressField.value;
                                searchField.value = results[0].formatted_address || searchField.value;
                            }
                            setMapLoader(false);
                        });
                        return;
                    }

                    try {
                        const response = await fetch(reverseGeocodeEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({ latitude, longitude })
                        });
                        const payload = await response.json();
                        if (payload?.result?.address) {
                            addressField.value = payload.result.address;
                            searchField.value = payload.result.address;
                        }
                    } catch (error) {
                    } finally {
                        setMapLoader(false);
                    }
                };

                const updateMap = (latitude, longitude, updateAddress = false) => {
                    const lat = Number(latitude);
                    const lng = Number(longitude);
                    if (!mapCanvas || !Number.isFinite(lat) || !Number.isFinite(lng)) {
                        return;
                    }

                    if (@json($canUseGoogleMaps) && window.google?.maps) {
                        if (!mapInstance) {
                            mapInstance = new google.maps.Map(mapCanvas, {
                                center: { lat, lng },
                                zoom: 16,
                                mapTypeControl: false,
                                streetViewControl: false,
                                fullscreenControl: false,
                            });
                            mapMarker = new google.maps.Marker({
                                map: mapInstance,
                                position: { lat, lng },
                                draggable: true,
                            });
                            mapMarker.addListener('dragend', () => {
                                const position = mapMarker.getPosition();
                                if (!position) {
                                    return;
                                }
                                latitudeField.value = String(position.lat());
                                longitudeField.value = String(position.lng());
                                reverseGeocode(position.lat(), position.lng());
                            });
                        }

                        mapInstance.setCenter({ lat, lng });
                        mapMarker.setPosition({ lat, lng });
                        if (updateAddress) {
                            reverseGeocode(lat, lng);
                        }
                        return;
                    }

                    if (typeof L !== 'undefined') {
                        if (!mapInstance) {
                            mapInstance = L.map(mapCanvas, { zoomControl: true }).setView([lat, lng], 16);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap',
                            }).addTo(mapInstance);
                            mapMarker = L.marker([lat, lng], { draggable: true }).addTo(mapInstance);
                            mapMarker.on('dragend', () => {
                                const markerLatLng = mapMarker.getLatLng();
                                latitudeField.value = String(markerLatLng.lat);
                                longitudeField.value = String(markerLatLng.lng);
                                reverseGeocode(markerLatLng.lat, markerLatLng.lng);
                            });
                        }

                        mapInstance.setView([lat, lng], 16);
                        mapMarker.setLatLng([lat, lng]);
                        setTimeout(() => mapInstance.invalidateSize(), 150);
                        if (updateAddress) {
                            reverseGeocode(lat, lng);
                        }
                    }
                };

                if (searchField && !@json($canUseGoogleMaps)) {
                    searchField.addEventListener('input', () => {
                        const searchText = searchField.value.trim();
                        const locationText = buildLocationText();
                        selectedFreePlace = null;

                        clearTimeout(searchTimeout);

                        if (searchText.length < 3) {
                            hideResults();
                            return;
                        }

                        searchTimeout = setTimeout(async () => {
                            try {
                                const endpoint = `${addressSearchEndpoint}?q=${encodeURIComponent(searchText)}&location=${encodeURIComponent(locationText)}`;
                                const response = await fetch(endpoint, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                                const payload = await response.json();
                                renderResults(Array.isArray(payload?.results) ? payload.results : []);
                            } catch (error) {
                                hideResults();
                            }
                        }, 350);
                    });

                    document.addEventListener('click', (event) => {
                        if (!resultsBox.contains(event.target) && event.target !== searchField) {
                            hideResults();
                        }
                    });

                    resultsBox.addEventListener('click', (event) => {
                        const button = event.target.closest('.js-address-result');
                        if (!button) {
                            return;
                        }

                        const selectedAddress = button.dataset.address || '';
                        searchField.value = selectedAddress;
                        addressField.value = selectedAddress;
                        setCoordinates(button.dataset.latitude || '', button.dataset.longitude || '');
                        selectedFreePlace = {
                            address: selectedAddress,
                            latitude: button.dataset.latitude || '',
                            longitude: button.dataset.longitude || '',
                        };
                        selectedGooglePlace = null;
                        helperText.textContent = @json(__('organization_address_selected_note'));
                        helperText.classList.remove('text-danger');
                        hideResults();
                    });
                }

                if (searchField && @json($canUseGoogleMaps) && window.google?.maps?.places) {
                    const autocomplete = new google.maps.places.Autocomplete(searchField, {
                        fields: ['formatted_address', 'geometry', 'name'],
                        types: ['geocode']
                    });

                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace();
                        selectedGooglePlace = place || null;
                        const selectedAddress = place?.formatted_address || searchField.value.trim();

                        addressField.value = selectedAddress;
                        helperText.textContent = @json(__('organization_address_selected_note'));
                        helperText.classList.remove('text-danger');

                        if (place?.geometry?.location) {
                            setCoordinates(place.geometry.location.lat(), place.geometry.location.lng());
                        }
                    });
                }

                if (fetchButton) {
                    fetchButton.addEventListener('click', async () => {
                        const chosenSearch = searchField ? searchField.value.trim() : '';
                        const address = addressField.value.trim() || chosenSearch;
                        const locationText = buildLocationText();

                        if (!address && !locationText) {
                            helperText.textContent = @json(__('organization_map_enter_address'));
                            helperText.classList.add('text-danger');
                            return;
                        }

                        helperText.textContent = @json(__('organization_map_fetching'));
                        helperText.classList.remove('text-danger');

                        try {
                            if (!@json($canUseGoogleMaps) && selectedFreePlace?.latitude && selectedFreePlace?.longitude) {
                                addressField.value = selectedFreePlace.address || address;
                                searchField.value = selectedFreePlace.address || address;
                                setCoordinates(selectedFreePlace.latitude, selectedFreePlace.longitude);
                                helperText.textContent = @json(__('organization_map_fetch_success'));
                                return;
                            }

                            if (@json($canUseGoogleMaps) && window.google?.maps?.Geocoder) {
                                if (selectedGooglePlace?.geometry?.location) {
                                    addressField.value = selectedGooglePlace.formatted_address || address;
                                    searchField.value = selectedGooglePlace.formatted_address || address;
                                    setCoordinates(selectedGooglePlace.geometry.location.lat(), selectedGooglePlace.geometry.location.lng());
                                    helperText.textContent = @json(__('organization_map_fetch_success'));
                                    return;
                                }

                                const geocoder = new google.maps.Geocoder();
                                geocoder.geocode({ address: [address, locationText].filter(Boolean).join(', ') }, (results, status) => {
                                    if (status === 'OK' && Array.isArray(results) && results.length) {
                                        const location = results[0].geometry?.location;
                                        addressField.value = results[0].formatted_address || address;
                                        searchField.value = results[0].formatted_address || address;
                                        setCoordinates(location ? location.lat() : '', location ? location.lng() : '');
                                        helperText.textContent = @json(__('organization_map_fetch_success'));
                                        helperText.classList.remove('text-danger');
                                    } else {
                                        helperText.textContent = @json(__('organization_map_fetch_empty'));
                                        helperText.classList.add('text-danger');
                                    }
                                });
                                return;
                            }

                            const endpoint = `${geocodeEndpoint}?address=${encodeURIComponent(address)}&location=${encodeURIComponent(locationText)}`;
                            const response = await fetch(endpoint, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const payload = await response.json();
                            const result = payload?.result || null;

                            if (result) {
                                if (!addressField.value.trim()) {
                                    addressField.value = result.address || address;
                                }

                                if (searchField && !searchField.value.trim()) {
                                    searchField.value = result.address || address;
                                }

                                setCoordinates(result.latitude || '', result.longitude || '');
                                helperText.textContent = @json(__('organization_map_fetch_success'));
                            } else {
                                helperText.textContent = @json(__('organization_map_fetch_empty'));
                                helperText.classList.add('text-danger');
                            }
                        } catch (error) {
                            helperText.textContent = @json(__('organization_map_fetch_error'));
                            helperText.classList.add('text-danger');
                        }
                    });
                }

                const initialLatitude = latitudeField.value || '24.8607';
                const initialLongitude = longitudeField.value || '67.0011';
                setCoordinates(initialLatitude, initialLongitude);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', window.initBranchAddressProvider, { once: true });
            } else {
                window.initBranchAddressProvider();
            }
        </script>
        @if($canUseGoogleMaps)
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsApiKey) }}&libraries=places&callback=initBranchAddressProvider"></script>
        @endif
    @endpush
