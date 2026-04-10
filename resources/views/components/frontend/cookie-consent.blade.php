@php
    $cookieBanner = \App\Models\Setting::cookieBannerConfig();
@endphp

@if($cookieBanner['enabled'] && !empty($cookieBanner['categories']))
<style>
.cookie-consent-overlay{position:fixed;inset:0;z-index:1200;background:rgba(0,0,0,.72);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;padding:20px}.cookie-consent-overlay.is-open{display:flex}.cookie-consent-modal{width:min(100%,730px);max-height:90vh;overflow:hidden;border-radius:24px;background:#fff;box-shadow:0 30px 80px rgba(0,0,0,.35);display:flex;flex-direction:column}.cookie-consent-scroll{overflow:auto;padding:28px 30px 22px}.cookie-consent-title{font-size:clamp(1.9rem,3vw,2.5rem);font-weight:400;color:#4b5563;text-align:center;margin:0 0 18px}.cookie-consent-copy{color:#5f6368;line-height:1.55;font-size:1rem}.cookie-consent-copy a,.cookie-consent-footer-links a,.cookie-category-details-toggle{color:#ff3b1f;text-decoration:none;font-weight:700}.cookie-consent-copy p{margin-bottom:1rem}.cookie-consent-copy p:last-child{margin-bottom:0}.cookie-consent-inline-checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:24px 0 18px}.cookie-check{display:flex;align-items:center;gap:12px;font-size:1rem;color:#4b5563}.cookie-check input{width:23px;height:23px}.cookie-check input:disabled{accent-color:#d1d5db}.cookie-consent-actions{display:grid;gap:12px}.cookie-consent-btn{width:100%;border:0;border-radius:8px;padding:14px 18px;font-size:1.08rem;font-weight:800;transition:.18s ease}.cookie-consent-btn--primary{background:#ff2d0a;color:#fff}.cookie-consent-btn--secondary{background:#f3f4f6;color:#6b7280}.cookie-consent-btn--dark{background:#000;color:#fff}.cookie-consent-footer-links{display:flex;justify-content:center;gap:16px;flex-wrap:wrap;margin-top:14px;font-size:.88rem;color:#9ca3af}.cookie-consent-settings-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:18px}.cookie-consent-settings-head .cookie-consent-btn{width:auto;padding:11px 22px;font-size:1rem}.cookie-consent-back{font-size:.92rem;color:#9ca3af;text-decoration:none}.cookie-category-card{background:#f7f7f7;border-radius:0;padding:18px 10px;margin-bottom:14px}.cookie-category-top{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}.cookie-category-title{font-size:1rem;font-weight:400;color:#5f6368;margin:0 0 8px}.cookie-category-description{color:#7a7d81;line-height:1.55;font-size:.95rem;margin:0}.cookie-toggle{display:inline-flex;align-items:center;gap:10px;font-size:.95rem;color:#5f6368;white-space:nowrap}.cookie-switch{position:relative;display:inline-block;width:56px;height:30px}.cookie-switch input{opacity:0;width:0;height:0}.cookie-switch-slider{position:absolute;cursor:pointer;inset:0;background:#d1d5db;border-radius:999px;transition:.2s}.cookie-switch-slider:before{position:absolute;content:"";height:24px;width:24px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 4px 10px rgba(0,0,0,.14)}.cookie-switch input:checked+.cookie-switch-slider{background:#b9bec7}.cookie-switch input:checked+.cookie-switch-slider:before{transform:translateX(26px)}.cookie-switch input:disabled+.cookie-switch-slider{background:#e5e7eb;cursor:not-allowed}.cookie-category-details-toggle{display:inline-block;margin-top:12px;font-size:.95rem}.cookie-details-wrap{display:none;margin-top:16px}.cookie-details-wrap.is-open{display:block}.cookie-details-table{width:100%;border-collapse:collapse;background:#fff}.cookie-details-table td{border:1px solid #ececec;padding:10px 12px;font-size:.96rem;color:#6b7280;vertical-align:top}.cookie-details-table td:first-child{width:38%;font-weight:800;color:#73777b;background:#fafafa}.cookie-pane{display:none}.cookie-pane.is-active{display:block}@media (max-width:767.98px){.cookie-consent-scroll{padding:18px 18px 16px}.cookie-consent-inline-checks{grid-template-columns:1fr}.cookie-category-top{flex-direction:column}.cookie-consent-settings-head{flex-wrap:wrap}.cookie-consent-settings-head .cookie-consent-btn{width:100%}}
</style>

<div class="cookie-consent-overlay" id="cookieConsentOverlay" aria-hidden="true">
    <div class="cookie-consent-modal" role="dialog" aria-modal="true" aria-labelledby="cookieConsentTitle">
        <div class="cookie-consent-scroll">
            <div class="cookie-pane is-active" data-cookie-pane="primary">
                <h2 class="cookie-consent-title" id="cookieConsentTitle">{{ $cookieBanner['title'] }}</h2>
                <div class="cookie-consent-copy">
                    {!! $cookieBanner['description'] !!}
                    <p>{{ $cookieBanner['notice'] }}</p>
                    @if($cookieBanner['privacy_link_url'])
                        <p>
                            <a href="{{ $cookieBanner['privacy_link_url'] }}" target="_blank" rel="noopener">{{ $cookieBanner['privacy_link_text'] }}</a>
                        </p>
                    @endif
                </div>

                <div class="cookie-consent-inline-checks">
                    @foreach($cookieBanner['categories'] as $category)
                        <label class="cookie-check">
                            <input type="checkbox"
                                   class="cookie-primary-checkbox"
                                   data-cookie-category="{{ $category['key'] }}"
                                   {{ !empty($category['default_selected']) ? 'checked' : '' }}
                                   {{ !empty($category['is_essential']) ? 'disabled' : '' }}>
                            <span>{{ $category['title'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="cookie-consent-actions">
                    <button type="button" class="cookie-consent-btn cookie-consent-btn--primary" id="cookieAcceptAllBtn">{{ $cookieBanner['accept_all_label'] }}</button>
                    <button type="button" class="cookie-consent-btn cookie-consent-btn--secondary" id="cookieSavePrimaryBtn">{{ $cookieBanner['save_label'] }}</button>
                    <button type="button" class="cookie-consent-btn cookie-consent-btn--dark" id="cookieOpenSettingsBtn">{{ $cookieBanner['manage_label'] }}</button>
                </div>

                <div class="cookie-consent-footer-links">
                    <a href="#" id="cookieFooterDetailsLink">Cookie Details</a>
                    @if($cookieBanner['privacy_link_url'])
                        <a href="{{ $cookieBanner['privacy_link_url'] }}" target="_blank" rel="noopener">{{ $cookieBanner['privacy_link_text'] }}</a>
                    @endif
                    @if($cookieBanner['imprint_link_url'])
                        <a href="{{ $cookieBanner['imprint_link_url'] }}" target="_blank" rel="noopener">{{ $cookieBanner['imprint_link_text'] }}</a>
                    @endif
                </div>
            </div>

            <div class="cookie-pane" data-cookie-pane="settings">
                <div class="cookie-consent-settings-head">
                    <div>
                        <h2 class="cookie-consent-title mb-0">{{ $cookieBanner['title'] }}</h2>
                    </div>
                    <a href="#" class="cookie-consent-back" id="cookieBackBtn">{{ $cookieBanner['back_label'] }}</a>
                </div>

                <div class="cookie-consent-copy">
                    {!! $cookieBanner['description'] !!}
                    <p>{{ $cookieBanner['notice'] }}</p>
                </div>

                <div class="cookie-consent-settings-head mt-4">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="cookie-consent-btn cookie-consent-btn--primary" id="cookieAcceptAllSettingsBtn">{{ $cookieBanner['accept_all_label'] }}</button>
                        <button type="button" class="cookie-consent-btn cookie-consent-btn--secondary" id="cookieSaveSettingsBtn">{{ $cookieBanner['save_label'] }}</button>
                    </div>
                    <a href="#" class="cookie-consent-back" id="cookieBackBtnTop">{{ $cookieBanner['back_label'] }}</a>
                </div>

                @foreach($cookieBanner['categories'] as $category)
                    <div class="cookie-category-card">
                        <div class="cookie-category-top">
                            <div>
                                <h3 class="cookie-category-title">{{ $category['title'] }}</h3>
                                <p class="cookie-category-description">{{ $category['description'] }}</p>
                            </div>
                            <label class="cookie-toggle">
                                <span>{{ !empty($category['is_essential']) ? 'On' : 'Off' }}</span>
                                <span class="cookie-switch">
                                    <input type="checkbox"
                                           class="cookie-settings-checkbox"
                                           data-cookie-category="{{ $category['key'] }}"
                                           {{ !empty($category['default_selected']) ? 'checked' : '' }}
                                           {{ !empty($category['is_essential']) ? 'disabled' : '' }}>
                                    <span class="cookie-switch-slider"></span>
                                </span>
                            </label>
                        </div>
                        @if(!empty($category['details']))
                            <a href="#" class="cookie-category-details-toggle" data-cookie-toggle-details="{{ $category['key'] }}">{{ $cookieBanner['show_details_label'] }}</a>
                            <div class="cookie-details-wrap" id="cookieDetails-{{ $category['key'] }}">
                                <table class="cookie-details-table">
                                    <tbody>
                                        @foreach($category['details'] as $detail)
                                            <tr>
                                                <td>{{ $detail['label'] }}</td>
                                                <td>
                                                    {{ $detail['value'] }}
                                                    @if(!empty($detail['link_text']) && !empty($detail['link_url']))
                                                        <a href="{{ $detail['link_url'] }}" target="_blank" rel="noopener">{{ $detail['link_text'] }}</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="cookie-consent-footer-links">
                    @if($cookieBanner['privacy_link_url'])
                        <a href="{{ $cookieBanner['privacy_link_url'] }}" target="_blank" rel="noopener">{{ $cookieBanner['privacy_link_text'] }}</a>
                    @endif
                    @if($cookieBanner['imprint_link_url'])
                        <a href="{{ $cookieBanner['imprint_link_url'] }}" target="_blank" rel="noopener">{{ $cookieBanner['imprint_link_text'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const overlay = document.getElementById('cookieConsentOverlay');
    if (!overlay) { return; }

    const storageKey = 'woofood_cookie_consent_v1';
    const categories = @json($cookieBanner['categories']);
    const consentDays = @json($cookieBanner['consent_days']);
    const showLabel = @json($cookieBanner['show_details_label']);
    const hideLabel = @json($cookieBanner['hide_details_label']);
    const primaryBoxes = Array.from(document.querySelectorAll('.cookie-primary-checkbox'));
    const settingsBoxes = Array.from(document.querySelectorAll('.cookie-settings-checkbox'));
    const panes = Array.from(document.querySelectorAll('[data-cookie-pane]'));

    const setPane = (name) => {
        panes.forEach((pane) => pane.classList.toggle('is-active', pane.dataset.cookiePane === name));
    };

    const syncCategory = (key, checked) => {
        primaryBoxes.filter((box) => box.dataset.cookieCategory === key).forEach((box) => {
            if (!box.disabled) { box.checked = checked; }
        });
        settingsBoxes.filter((box) => box.dataset.cookieCategory === key).forEach((box) => {
            if (!box.disabled) { box.checked = checked; }
        });
    };

    const getState = () => {
        const state = {};
        categories.forEach((category) => {
            const key = category.key;
            const essential = !!category.is_essential;
            const box = primaryBoxes.find((item) => item.dataset.cookieCategory === key) || settingsBoxes.find((item) => item.dataset.cookieCategory === key);
            state[key] = essential ? true : !!box?.checked;
        });
        return state;
    };

    const applyState = (state) => {
        categories.forEach((category) => {
            const checked = category.is_essential ? true : !!state?.[category.key];
            syncCategory(category.key, checked);
        });
    };

    const setAll = (value) => {
        categories.forEach((category) => syncCategory(category.key, category.is_essential ? true : value));
    };

    const saveConsent = () => {
        const payload = {
            saved_at: new Date().toISOString(),
            categories: getState(),
        };
        localStorage.setItem(storageKey, JSON.stringify(payload));
        const expires = new Date();
        expires.setDate(expires.getDate() + consentDays);
        document.cookie = `site_cookie_consent=${encodeURIComponent(JSON.stringify(payload.categories))}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
    };

    const existing = localStorage.getItem(storageKey);
    if (!existing) {
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        setPane('primary');
    } else {
        try {
            const parsed = JSON.parse(existing);
            applyState(parsed.categories || {});
        } catch (error) {
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            setPane('primary');
        }
    }

    primaryBoxes.forEach((box) => box.addEventListener('change', function () { syncCategory(this.dataset.cookieCategory, this.checked); }));
    settingsBoxes.forEach((box) => box.addEventListener('change', function () { syncCategory(this.dataset.cookieCategory, this.checked); }));

    document.getElementById('cookieAcceptAllBtn')?.addEventListener('click', () => { setAll(true); saveConsent(); });
    document.getElementById('cookieAcceptAllSettingsBtn')?.addEventListener('click', () => { setAll(true); saveConsent(); });
    document.getElementById('cookieSavePrimaryBtn')?.addEventListener('click', saveConsent);
    document.getElementById('cookieSaveSettingsBtn')?.addEventListener('click', saveConsent);
    document.getElementById('cookieOpenSettingsBtn')?.addEventListener('click', () => setPane('settings'));
    document.getElementById('cookieFooterDetailsLink')?.addEventListener('click', (event) => { event.preventDefault(); setPane('settings'); });
    document.getElementById('cookieBackBtn')?.addEventListener('click', (event) => { event.preventDefault(); setPane('primary'); });
    document.getElementById('cookieBackBtnTop')?.addEventListener('click', (event) => { event.preventDefault(); setPane('primary'); });

    document.querySelectorAll('[data-cookie-toggle-details]').forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const key = this.dataset.cookieToggleDetails;
            const wrap = document.getElementById(`cookieDetails-${key}`);
            if (!wrap) { return; }
            const isOpen = wrap.classList.toggle('is-open');
            this.textContent = isOpen ? hideLabel : showLabel;
        });
    });

    window.cookieConsentManager = {
        getConsent: getState,
        accepts: (key) => !!getState()[key],
        openSettings: () => {
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            setPane('settings');
        },
        reset: () => {
            localStorage.removeItem(storageKey);
            document.cookie = 'site_cookie_consent=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;';
            setAll(false);
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            setPane('primary');
        }
    };
})();
</script>
@endif
