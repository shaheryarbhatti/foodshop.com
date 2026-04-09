@extends('layouts.frontend')

@section('title', 'My Profile')

@php
    $activeNav = 'profile';
    $customerImage = $customer->image ? asset('public/storage/' . $customer->image) : null;
    $mapProvider = \App\Models\Setting::get('map_provider', 'leaflet');
    $googleMapsApiKey = \App\Models\Setting::get('google_maps_api_key', '');
@endphp

@section('styles')
<link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
@if($mapProvider === 'leaflet')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endif
<style>
.profile-page{padding:36px 0 56px}
.profile-shell{display:grid;grid-template-columns:300px minmax(0,1fr);gap:24px}
.profile-sidebar,.profile-panel{border-radius:28px;background:rgba(255,255,255,.96);border:1px solid rgba(15,23,42,.08);box-shadow:0 26px 54px rgba(15,23,42,.12)}
.profile-sidebar{padding:24px;position:sticky;top:24px;height:fit-content}
.profile-avatar{width:86px;height:86px;border-radius:26px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary));color:#fff;font-size:2rem;box-shadow:0 18px 34px rgba(15,23,42,.14)}
.profile-avatar img{width:100%;height:100%;object-fit:cover}
.profile-kicker{margin:18px 0 4px;color:#64748b;font-size:.84rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.profile-name{margin:0;color:#0f172a;font-size:1.65rem;font-weight:900;letter-spacing:-.03em}
.profile-email{margin-top:8px;color:#475569;font-weight:700;word-break:break-word}
.profile-links{margin-top:22px;display:grid;gap:10px}
.profile-link{display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:18px;background:#fff;color:#1f2937;text-decoration:none;font-weight:800;border:1px solid rgba(15,23,42,.08);box-shadow:0 10px 18px rgba(15,23,42,.05)}
.profile-link.is-active{background:linear-gradient(135deg,var(--portal-primary),#f08b3f);color:#fff;border-color:transparent}
.profile-link i{width:18px;text-align:center}
.profile-link--logout{border:0;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;justify-content:flex-start}
.profile-panel{padding:28px}
.profile-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start}
.profile-hero h1{margin:0;color:#0f172a;font-size:clamp(2rem,3vw,2.7rem);font-weight:900;letter-spacing:-.04em}
.profile-hero p{margin:10px 0 0;color:#64748b;max-width:700px;font-size:1rem;line-height:1.7}
.profile-badge{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border-radius:999px;background:linear-gradient(135deg,rgba(255,191,31,.22),rgba(203,43,29,.12));color:#8d411e;font-size:.9rem;font-weight:900}
.profile-form-card{margin-top:24px;padding:26px;border-radius:26px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 16px 32px rgba(15,23,42,.05)}
.profile-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.profile-field-full{grid-column:1/-1}
.profile-label{font-weight:800;color:#2b3445;margin-bottom:8px}
.profile-control,.profile-select{min-height:52px;border-radius:14px;border:1px solid rgba(15,23,42,.12);box-shadow:none}
.profile-control:focus,.profile-select:focus{border-color:rgba(203,43,29,.48);box-shadow:0 0 0 3px rgba(203,43,29,.1)}
.profile-textarea{min-height:120px}
.profile-photo-card{padding:18px;border-radius:22px;background:linear-gradient(135deg,#fff8f1,#ffffff);border:1px solid rgba(15,23,42,.06)}
.profile-photo-preview{width:116px;height:116px;border-radius:28px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary));color:#fff;font-size:2.3rem;box-shadow:0 18px 34px rgba(15,23,42,.12)}
.profile-photo-preview img{width:100%;height:100%;object-fit:cover}
.profile-note{margin-top:10px;color:#64748b;line-height:1.6}
.profile-submit{display:inline-flex;align-items:center;justify-content:center;min-height:54px;padding:0 28px;border:0;border-radius:999px;background:linear-gradient(135deg,var(--portal-primary),#f08b3f);color:#fff;font-weight:900;box-shadow:0 14px 26px rgba(15,23,42,.12)}
.profile-submit:hover{color:#fff;filter:brightness(.98)}
.select2-container--default .select2-selection--single {
    min-height: 52px;
    border-radius: 14px;
    border: 1px solid rgba(15,23,42,0.12);
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    padding-left: 15px;
    color: #0f172a;
    font-weight: 700;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 50px;
}
.address-map-card{position:relative;margin-top:12px;border:1px solid rgba(15,23,42,.12);border-radius:18px;background:#fff;overflow:hidden;box-shadow:0 14px 28px rgba(15,23,42,.08)}
.address-map-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:#f8fafc;border-bottom:1px solid rgba(15,23,42,.08)}
.address-map-head span{font-size:.96rem;font-weight:800;color:#1f2937}
.address-map-head small{font-size:.8rem;color:#64748b}
.address-map-canvas{height:280px;width:100%;background:linear-gradient(180deg,#eff6ff,#f8fafc)}
.address-map-loader{position:absolute;inset:auto 16px 16px auto;display:none;align-items:center;gap:10px;padding:10px 14px;border-radius:999px;background:rgba(15,23,42,.88);color:#fff;font-size:.84rem;font-weight:700;box-shadow:0 10px 22px rgba(15,23,42,.24);z-index:1000}
.address-map-loader.is-visible{display:inline-flex}
.address-map-loader i{font-size:.95rem}
.address-search-wrap{position:relative}
.address-suggestions{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid rgba(15,23,42,.12);border-radius:14px;margin-top:6px;box-shadow:0 18px 34px rgba(15,23,42,.12);z-index:2000;display:none;max-height:300px;overflow-y:auto}
.address-suggestions.is-visible{display:block}
.address-suggestion{padding:12px 16px;cursor:pointer;border-bottom:1px solid rgba(15,23,42,.06);transition:background .2s ease}
.address-suggestion:last-child{border-bottom:0}
.address-suggestion:hover{background:#f8fafc}
.address-suggestion-title{display:block;font-size:.95rem;font-weight:700;color:#111827;margin-bottom:2px}
.address-suggestion-copy{display:block;font-size:.82rem;color:#64748b}
@media (max-width:991.98px){.profile-shell{grid-template-columns:1fr}.profile-sidebar{position:static}.profile-form-grid{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
<section class="profile-page">
    <div class="container">
        <div class="profile-shell">
            <aside class="profile-sidebar">
                <div class="profile-avatar">
                    @if($customerImage)
                        <img src="{{ $customerImage }}" alt="Profile">
                    @else
                        <i class="fa-solid fa-user"></i>
                    @endif
                </div>
                <div class="profile-kicker">Customer Portal</div>
                <h2 class="profile-name">{{ $customer->full_name }}</h2>
                <div class="profile-email">{{ $customer->email }}</div>

                <div class="profile-links">
                    <a href="{{ route('frontend.dashboard') }}" class="profile-link">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('frontend.profile') }}" class="profile-link is-active">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Profile</span>
                    </a>
                    <form method="POST" action="{{ route('frontend.logout') }}">
                        @csrf
                        <button type="submit" class="profile-link profile-link--logout w-100">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="profile-panel">
                <div class="profile-hero">
                    <div>
                        <h1>Profile</h1>
                        <p>Keep your delivery details accurate, refresh your contact information, and personalize your customer account with a profile image.</p>
                    </div>
                    <div class="profile-badge">
                        <i class="fa-solid fa-pen-ruler"></i>
                        <span>Update your account details anytime</span>
                    </div>
                </div>

                @if (session('status'))
                    <div class="alert alert-success mt-4 mb-0">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mt-4 mb-0">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('frontend.profile.update') }}" enctype="multipart/form-data" class="profile-form-card">
                    @csrf

                    <div class="profile-form-grid">
                        <div class="profile-field-full profile-photo-card d-flex flex-column flex-md-row align-items-md-center gap-4">
                            <div class="profile-photo-preview">
                                @if($customerImage)
                                    <img src="{{ $customerImage }}" alt="Profile preview" id="profilePreviewImage">
                                @else
                                    <img src="" alt="Profile preview" id="profilePreviewImage" style="display:none;">
                                    <i class="fa-solid fa-user" id="profilePreviewIcon"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="profile-label" for="image">Profile Image</label>
                                <input type="file" name="image" id="image" class="form-control profile-control" accept=".jpg,.jpeg,.png,.webp">
                                <div class="profile-note">Upload a clear profile photo. Supported formats: JPG, PNG, WEBP. Max size 2MB.</div>
                            </div>
                        </div>

                        <div>
                            <label class="profile-label" for="customer_type">Customer Type</label>
                            <select name="customer_type" id="customer_type" class="form-select profile-select">
                                <option value="individual" {{ old('customer_type', $customer->customer_type) === 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="company" {{ old('customer_type', $customer->customer_type) === 'company' ? 'selected' : '' }}>Company</option>
                            </select>
                        </div>

                        <div>
                            <label class="profile-label" for="company_name">Company Name</label>
                            <input type="text" name="company_name" id="company_name" class="form-control profile-control" value="{{ old('company_name', $customer->company_name) }}">
                        </div>

                        <div>
                            <label class="profile-label" for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control profile-control" value="{{ old('first_name', $customer->first_name) }}" required>
                        </div>

                        <div>
                            <label class="profile-label" for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control profile-control" value="{{ old('last_name', $customer->last_name) }}" required>
                        </div>

                        <div>
                            <label class="profile-label" for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control profile-control" value="{{ old('email', $customer->email) }}" required>
                        </div>

                        <div>
                            <label class="profile-label" for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control profile-control" value="{{ old('phone', $customer->phone) }}" required>
                        </div>

                        <div class="profile-field-full">
                            <label class="profile-label" for="address">Address Search</label>
                            <div class="address-search-wrap">
                                <input type="text" name="address" id="profileAddressSearch" class="form-control profile-control" value="{{ old('address', $customer->address) }}" placeholder="Search your address..." required>
                                <div class="address-suggestions" id="profileAddressSuggestions"></div>
                            </div>
                        </div>

                        <div class="profile-field-full">
                            <div class="address-map-card">
                                <div class="address-map-head">
                                    <span>Pin Point Location</span>
                                    <small>Drag the marker to refine your delivery spot</small>
                                </div>
                                <div id="profileAddressMap" class="address-map-canvas"></div>
                                <div class="address-map-loader" id="profileAddressMapLoader">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <span>Updating location...</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="profile-label" for="city">City</label>
                            <input type="text" name="city" id="profileCity" class="form-control profile-control" value="{{ old('city', $customer->city) }}" required>
                        </div>

                        <div>
                            <label class="profile-label" for="postal_code">Postal Code</label>
                            <input type="text" name="postal_code" id="profilePostalCode" class="form-control profile-control" value="{{ old('postal_code', $customer->postal_code) }}" required>
                        </div>

                        <div>
                            <label class="profile-label" for="country_id">Country</label>
                            <select name="country_id" id="profileCountrySelect" class="form-select profile-select js-select2" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" data-country-name="{{ strtolower($country->name) }}" {{ (string) old('country_id', $customer->country_id) === (string) $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="latitude" id="profileLatitude" value="{{ old('latitude', $customer->latitude) }}">
                        <input type="hidden" name="longitude" id="profileLongitude" value="{{ old('longitude', $customer->longitude) }}">
                        <input type="hidden" name="country" id="profileCountryName" value="{{ old('country', $customer->country) }}">

                        <div>
                            <label class="profile-label" for="password">New Password</label>
                            <input type="password" name="password" id="password" class="form-control profile-control">
                        </div>

                        <div>
                            <label class="profile-label" for="password_confirmation">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control profile-control">
                        </div>

                        <div class="profile-field-full text-end pt-2">
                            <button type="submit" class="profile-submit">Save Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@if($mapProvider === 'leaflet')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif
@if($mapProvider === 'google' && filled($googleMapsApiKey))
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
@endif
<script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
<script>
(() => {
    const mapProvider = @json($mapProvider);
    const routes = {
        addressSearch: @json(route('frontend.address-search')),
        geocodeAddress: @json(route('frontend.geocode-address')),
        reverseGeocodeAddress: @json(route('frontend.reverse-geocode-address'))
    };

    const imageInput = document.getElementById('image');
    const previewImage = document.getElementById('profilePreviewImage');
    const previewIcon = document.getElementById('profilePreviewIcon');

    const addressSearchField = document.getElementById('profileAddressSearch');
    const addressSuggestionsEl = document.getElementById('profileAddressSuggestions');
    const cityField = document.getElementById('profileCity');
    const postalField = document.getElementById('profilePostalCode');
    const countrySelect = document.getElementById('profileCountrySelect');
    const countryNameField = document.getElementById('profileCountryName');
    const latitudeField = document.getElementById('profileLatitude');
    const longitudeField = document.getElementById('profileLongitude');
    const mapCanvas = document.getElementById('profileAddressMap');
    const mapLoader = document.getElementById('profileAddressMapLoader');

    let addressMap = null, addressMarker = null, addressSearchTimer = null, addressReverseLookupTimer = null, googleAutocomplete = null;

    // --- Core Map Functions ---
    function setMapLoader(visible) { if(mapLoader) mapLoader.classList.toggle('is-visible', visible); }

    function updateMapFromCoordinates(latitude, longitude, withLookup = false) {
        const lat = Number(latitude), lng = Number(longitude);
        if (!mapCanvas || !Number.isFinite(lat) || !Number.isFinite(lng)) return;

        if (mapProvider === 'google' && typeof google !== 'undefined' && google.maps) {
            if (!addressMap) {
                addressMap = new google.maps.Map(mapCanvas, { center: {lat, lng}, zoom: 16, mapTypeControl: false });
                addressMarker = new google.maps.Marker({ map: addressMap, position: {lat, lng}, draggable: true });
                addressMarker.addListener('dragend', () => {
                    const pos = addressMarker.getPosition();
                    latitudeField.value = pos.lat();
                    longitudeField.value = pos.lng();
                    scheduleReverseGeocode(pos.lat(), pos.lng());
                });
            } else {
                addressMap.setCenter({lat, lng});
                addressMarker.setPosition({lat, lng});
            }
            if (withLookup) scheduleReverseGeocode(lat, lng);
        } else if (mapProvider === 'leaflet' && typeof L !== 'undefined') {
            if (!addressMap) {
                addressMap = L.map(mapCanvas).setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(addressMap);
                addressMarker = L.marker([lat, lng], {draggable: true}).addTo(addressMap);
                addressMarker.on('dragend', () => {
                    const pos = addressMarker.getLatLng();
                    latitudeField.value = pos.lat;
                    longitudeField.value = pos.lng;
                    scheduleReverseGeocode(pos.lat, pos.lng);
                });
            } else {
                addressMap.setView([lat, lng], 16);
                addressMarker.setLatLng([lat, lng]);
            }
            if (withLookup) scheduleReverseGeocode(lat, lng);
            setTimeout(() => addressMap.invalidateSize(), 200);
        }
    }

    function scheduleReverseGeocode(lat, lng) {
        clearTimeout(addressReverseLookupTimer);
        setMapLoader(true);
        addressReverseLookupTimer = setTimeout(async () => {
            try {
                const response = await fetch(routes.reverseGeocodeAddress, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({latitude: lat, longitude: lng})
                });
                const data = await response.json();
                if (data.result) fillAddressFields(data.result, false);
            } catch (e) {} finally { setMapLoader(false); }
        }, 500);
    }

    function fillAddressFields(result, updateMap = true) {
        if (!result) return;
        addressSearchField.value = result.address || result.label || '';
        cityField.value = result.city || '';
        postalField.value = result.postal_code || '';
        latitudeField.value = result.latitude || '';
        longitudeField.value = result.longitude || '';
        if (result.country) setCountryByName(result.country);
        if (updateMap && result.latitude && result.longitude) updateMapFromCoordinates(result.latitude, result.longitude, false);
    }

    function setCountryByName(name) {
        const normalized = name.trim().toLowerCase();
        const option = Array.from(countrySelect.options).find(o => o.dataset.countryName === normalized || o.text.toLowerCase() === normalized);
        if (option) {
            jQuery(countrySelect).val(option.value).trigger('change');
            countryNameField.value = option.text;
        }
    }

    // --- Search Functions ---
    async function searchLeafletAddresses(query) {
        const response = await fetch(`${routes.addressSearch}?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        renderSuggestions(data.results || []);
    }

    function renderSuggestions(results) {
        if (!addressSuggestionsEl) return;
        if (!results.length) { addressSuggestionsEl.classList.remove('is-visible'); return; }
        addressSuggestionsEl.innerHTML = results.map((r, i) => `
            <div class="address-suggestion" data-index="${i}">
                <span class="address-suggestion-title">${r.title || r.address}</span>
                <span class="address-suggestion-copy">${r.label || ''}</span>
            </div>
        `).join('');
        addressSuggestionsEl.classList.add('is-visible');
        addressSuggestionsEl.dataset.results = JSON.stringify(results);
    }

    // --- Event Listeners ---
    if (imageInput && previewImage) {
        imageInput.addEventListener('change', (event) => {
            const [file] = event.target.files || [];
            if (!file) return;
            const objectUrl = URL.createObjectURL(file);
            previewImage.src = objectUrl; previewImage.style.display = 'block';
            if (previewIcon) previewIcon.style.display = 'none';
            previewImage.onload = () => URL.revokeObjectURL(objectUrl);
        });
    }

    if (window.jQuery && jQuery.fn.select2) {
        jQuery('.js-select2').select2({ width: '100%', placeholder: 'Select Country' })
            .on('change', function() { countryNameField.value = this.options[this.selectedIndex]?.text || ''; });
    }

    if (mapProvider === 'google' && typeof google !== 'undefined' && google.maps.places) {
        googleAutocomplete = new google.maps.places.Autocomplete(addressSearchField, { types: ['geocode'] });
        googleAutocomplete.addListener('place_changed', () => {
            const place = googleAutocomplete.getPlace();
            if (!place.geometry) return;
            const lat = place.geometry.location.lat(), lng = place.geometry.location.lng();
            latitudeField.value = lat; longitudeField.value = lng;
            updateMapFromCoordinates(lat, lng, false);
        });
    } else {
        addressSearchField.addEventListener('input', () => {
            clearTimeout(addressSearchTimer);
            const q = addressSearchField.value.trim();
            if (q.length < 3) { addressSuggestionsEl.classList.remove('is-visible'); return; }
            addressSearchTimer = setTimeout(() => searchLeafletAddresses(q), 300);
        });
    }

    document.addEventListener('click', (e) => {
        const item = e.target.closest('.address-suggestion');
        if (item) {
            const results = JSON.parse(addressSuggestionsEl.dataset.results);
            fillAddressFields(results[item.dataset.index]);
            addressSuggestionsEl.classList.remove('is-visible');
        } else if (!e.target.closest('.address-search-wrap')) {
            addressSuggestionsEl.classList.remove('is-visible');
        }
    });

    // --- Initialization ---
    const initialLat = latitudeField.value || 24.8607, initialLng = longitudeField.value || 67.0011;
    updateMapFromCoordinates(initialLat, initialLng, false);
})();
</script>
@endsection
