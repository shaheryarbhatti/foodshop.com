<style>
.cookie-setting-card{border:1px solid #e8edf5;border-radius:20px;background:linear-gradient(180deg,#ffffff,#fbfcff);box-shadow:0 12px 30px rgba(18,38,63,.06);padding:20px}.cookie-category-card,.cookie-detail-card{border:1px solid #e8edf5;border-radius:18px;background:#fff;padding:18px}.cookie-category-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:16px}.cookie-category-title{font-size:1rem;font-weight:800;margin:0}.cookie-category-subtitle{font-size:.84rem;color:#7b8795;margin-top:4px}.cookie-detail-list{display:grid;gap:12px}.cookie-detail-card{background:#f8fafc}.cookie-settings-empty{padding:24px;border:1px dashed #d6deea;border-radius:18px;text-align:center;color:#7c8b9d;background:#fbfcff}.cookie-banner-preview{border-radius:22px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(180deg,#ffffff,#fafafa);padding:24px;box-shadow:0 20px 45px rgba(15,23,42,.08)}.cookie-banner-preview__title{font-size:1.35rem;font-weight:900;color:#0f172a;margin-bottom:10px}.cookie-banner-preview__text{color:#64748b;line-height:1.75;margin-bottom:16px}.cookie-banner-preview__text p{margin-bottom:.85rem}.cookie-banner-preview__text p:last-child{margin-bottom:0}.cookie-preview-checks{display:flex;flex-wrap:wrap;gap:18px;margin-bottom:18px}.cookie-preview-actions{display:grid;gap:10px}.cookie-preview-actions .btn{border-radius:12px;font-weight:800}.cookie-preview-links{display:flex;flex-wrap:wrap;gap:12px;margin-top:14px;font-size:.88rem}.cookie-preview-links a{color:#7b8795;text-decoration:none}.cookie-setting-card .ck-editor__editable_inline{min-height:180px}
</style>

<div class="row settings-section" id="settings-cookie-settings">
    <div class="col-12 mb-2">
        <h5 class="mb-1">Cookie Settings</h5>
        <p class="text-muted mb-0">Create the frontend cookie popup, configure consent text, manage legal links, and define which cookie categories are essential or optional.</p>
    </div>
    <div class="col-12">
        <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="cookie-setting-card">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input mt-0" name="cookie_banner_enabled" value="1" {{ $cookieBannerEnabled ? 'checked' : '' }}>
                                    <span>Enable cookie popup on frontend</span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Popup Title</label>
                                <input type="text" name="cookie_banner_title" class="form-control" value="{{ $cookieBannerConfig['title'] }}" placeholder="Privacy settings">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Consent Storage Days</label>
                                <input type="number" min="1" max="3650" name="cookie_consent_storage_days" class="form-control" value="{{ $cookieBannerConfig['consent_days'] }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Main Description</label>
                                <textarea id="cookieBannerDescriptionEditor" name="cookie_banner_description" class="form-control cookie-ckeditor-field" rows="3" placeholder="Explain why cookies are used and how visitors can control them.">{{ $cookieBannerConfig['description'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Extra Notice</label>
                                <textarea name="cookie_banner_notice" class="form-control" rows="3" placeholder="Add an extra legal or privacy note shown in the first popup.">{{ $cookieBannerConfig['notice'] }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Accept All Button Label</label>
                                <input type="text" name="cookie_accept_all_label" class="form-control" value="{{ $cookieBannerConfig['accept_all_label'] }}" placeholder="Accept all">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Save Button Label</label>
                                <input type="text" name="cookie_save_label" class="form-control" value="{{ $cookieBannerConfig['save_label'] }}" placeholder="Save">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Manage Button Label</label>
                                <input type="text" name="cookie_manage_label" class="form-control" value="{{ $cookieBannerConfig['manage_label'] }}" placeholder="Individual Privacy Settings">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Back Button Label</label>
                                <input type="text" name="cookie_back_label" class="form-control" value="{{ $cookieBannerConfig['back_label'] }}" placeholder="Back">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Show Details Label</label>
                                <input type="text" name="cookie_show_details_label" class="form-control" value="{{ $cookieBannerConfig['show_details_label'] }}" placeholder="Show cookie information">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Hide Details Label</label>
                                <input type="text" name="cookie_hide_details_label" class="form-control" value="{{ $cookieBannerConfig['hide_details_label'] }}" placeholder="Hide cookie information">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Privacy Policy Link Text</label>
                                <input type="text" name="cookie_privacy_link_text" class="form-control" value="{{ $cookieBannerConfig['privacy_link_text'] }}" placeholder="Privacy Policy">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Privacy Policy URL</label>
                                <input type="text" name="cookie_privacy_link_url" class="form-control" value="{{ $cookieBannerConfig['privacy_link_url'] }}" placeholder="https://example.com/privacy-policy">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Imprint Link Text</label>
                                <input type="text" name="cookie_imprint_link_text" class="form-control" value="{{ $cookieBannerConfig['imprint_link_text'] }}" placeholder="Imprint">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Imprint URL</label>
                                <input type="text" name="cookie_imprint_link_url" class="form-control" value="{{ $cookieBannerConfig['imprint_link_url'] }}" placeholder="https://example.com/imprint">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="cookie-banner-preview">
                        <div class="cookie-banner-preview__title">{{ $cookieBannerConfig['title'] }}</div>
                        <div class="cookie-banner-preview__text" id="cookieBannerDescriptionPreview">{!! $cookieBannerConfig['description'] !!}</div>
                        <div class="cookie-banner-preview__text">{{ $cookieBannerConfig['notice'] }}</div>
                        <div class="cookie-preview-checks">
                            @foreach($cookieCategories as $category)
                                <label class="d-inline-flex align-items-center gap-2 fw-semibold">
                                    <input type="checkbox" class="form-check-input mt-0" {{ !empty($category['default_selected']) ? 'checked' : '' }} {{ !empty($category['is_essential']) ? 'disabled' : '' }}>
                                    <span>{{ $category['title'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="cookie-preview-actions">
                            <button type="button" class="btn btn-primary">{{ $cookieBannerConfig['accept_all_label'] }}</button>
                            <button type="button" class="btn btn-light border">{{ $cookieBannerConfig['save_label'] }}</button>
                            <button type="button" class="btn btn-dark">{{ $cookieBannerConfig['manage_label'] }}</button>
                        </div>
                        <div class="cookie-preview-links">
                            <a href="javascript:void(0)">{{ $cookieBannerConfig['privacy_link_text'] }}</a>
                            <a href="javascript:void(0)">Cookie Details</a>
                            <a href="javascript:void(0)">{{ $cookieBannerConfig['imprint_link_text'] }}</a>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="cookie-setting-card">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                            <div>
                                <h6 class="mb-1 fw-bold">Cookie Categories and Detail Table</h6>
                                <p class="text-muted mb-0">Create categories like Essential or External Media, decide which ones are essential, and add detailed rows for the cookie information table.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addCookieCategoryBtn" {{ $guestMode ? 'disabled' : '' }}>
                                <i class="fa fa-plus me-1"></i>Add Cookie Category
                            </button>
                        </div>

                        <input type="hidden" name="cookie_categories_json" id="cookieCategoriesJson">
                        <div class="row g-4" id="cookieCategoriesEditor">
                            @foreach($cookieCategories as $index => $category)
                                <div class="col-12 cookie-category-item">
                                    <div class="cookie-category-card" data-cookie-category-id="{{ $category['id'] }}">
                                        <div class="cookie-category-head">
                                            <div>
                                                <h6 class="cookie-category-title">{{ $category['title'] }}</h6>
                                                <div class="cookie-category-subtitle">Configure labels, selection behaviour, and cookie details for this category.</div>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm cookie-category-remove">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Title</label>
                                                <input type="text" class="form-control cookie-category-title-input" value="{{ $category['title'] }}" placeholder="Essential">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Key</label>
                                                <input type="text" class="form-control cookie-category-key-input" value="{{ $category['key'] }}" placeholder="essential">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold d-flex align-items-center gap-2">
                                                    <input type="checkbox" class="form-check-input mt-0 cookie-category-essential" {{ !empty($category['is_essential']) ? 'checked' : '' }}>
                                                    <span>Essential Cookie</span>
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold d-flex align-items-center gap-2">
                                                    <input type="checkbox" class="form-check-input mt-0 cookie-category-default-selected" {{ !empty($category['default_selected']) ? 'checked' : '' }} {{ !empty($category['is_essential']) ? 'disabled' : '' }}>
                                                    <span>Selected by default</span>
                                                </label>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Description</label>
                                                <textarea class="form-control cookie-category-description-input" rows="2" placeholder="Explain what this category does.">{{ $category['description'] }}</textarea>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                                <h6 class="mb-0 fw-bold">Cookie Detail Rows</h6>
                                                <button type="button" class="btn btn-outline-primary btn-sm cookie-detail-add">
                                                    <i class="fa fa-plus me-1"></i>Add Detail Row
                                                </button>
                                            </div>
                                            <div class="cookie-detail-list">
                                                @foreach($category['details'] as $detail)
                                                    <div class="cookie-detail-card">
                                                        <div class="row g-3">
                                                            <div class="col-md-3">
                                                                <label class="form-label fw-bold">Label</label>
                                                                <input type="text" class="form-control cookie-detail-label" value="{{ $detail['label'] }}" placeholder="Provider">
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="form-label fw-bold">Value</label>
                                                                <input type="text" class="form-control cookie-detail-value" value="{{ $detail['value'] }}" placeholder="Owner of this website">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label fw-bold">Link Text</label>
                                                                <input type="text" class="form-control cookie-detail-link-text" value="{{ $detail['link_text'] }}" placeholder="legal notice">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label fw-bold">Link URL</label>
                                                                <input type="text" class="form-control cookie-detail-link-url" value="{{ $detail['link_url'] }}" placeholder="/privacy-policy">
                                                            </div>
                                                            <div class="col-12 text-end">
                                                                <button type="button" class="btn btn-outline-danger btn-sm cookie-detail-remove"><i class="fa fa-trash me-1"></i>Remove Row</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="cookie-settings-empty mt-3 {{ count($cookieCategories) ? 'd-none' : '' }}" id="cookieCategoriesEmpty">
                            No cookie categories added yet. Create at least one essential category and one optional category like external media.
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<script>
(function () {
    const editor = document.getElementById('cookieCategoriesEditor');
    const hiddenInput = document.getElementById('cookieCategoriesJson');
    const addButton = document.getElementById('addCookieCategoryBtn');
    const emptyState = document.getElementById('cookieCategoriesEmpty');
    if (!editor || !hiddenInput) { return; }

    const slugify = (value) => String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');

    const createDetailRow = () => {
        const wrap = document.createElement('div');
        wrap.className = 'cookie-detail-card';
        wrap.innerHTML = `<div class="row g-3"><div class="col-md-3"><label class="form-label fw-bold">Label</label><input type="text" class="form-control cookie-detail-label" placeholder="Cookie Name"></div><div class="col-md-5"><label class="form-label fw-bold">Value</label><input type="text" class="form-control cookie-detail-value" placeholder="site_cookie_consent"></div><div class="col-md-2"><label class="form-label fw-bold">Link Text</label><input type="text" class="form-control cookie-detail-link-text" placeholder="legal notice"></div><div class="col-md-2"><label class="form-label fw-bold">Link URL</label><input type="text" class="form-control cookie-detail-link-url" placeholder="/privacy-policy"></div><div class="col-12 text-end"><button type="button" class="btn btn-outline-danger btn-sm cookie-detail-remove"><i class="fa fa-trash me-1"></i>Remove Row</button></div></div>`;
        return wrap;
    };

    const createCategoryCard = () => {
        const wrap = document.createElement('div');
        wrap.className = 'col-12 cookie-category-item';
        wrap.innerHTML = `<div class="cookie-category-card" data-cookie-category-id="cookie_${Date.now()}_${Math.random().toString(36).slice(2, 7)}"><div class="cookie-category-head"><div><h6 class="cookie-category-title">Cookie Category</h6><div class="cookie-category-subtitle">Configure labels, selection behaviour, and cookie details for this category.</div></div><button type="button" class="btn btn-outline-danger btn-sm cookie-category-remove"><i class="fa fa-trash"></i></button></div><div class="row g-3"><div class="col-md-3"><label class="form-label fw-bold">Title</label><input type="text" class="form-control cookie-category-title-input" placeholder="External Media"></div><div class="col-md-3"><label class="form-label fw-bold">Key</label><input type="text" class="form-control cookie-category-key-input" placeholder="external_media"></div><div class="col-md-3"><label class="form-label fw-bold d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input mt-0 cookie-category-essential"><span>Essential Cookie</span></label></div><div class="col-md-3"><label class="form-label fw-bold d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input mt-0 cookie-category-default-selected"><span>Selected by default</span></label></div><div class="col-12"><label class="form-label fw-bold">Description</label><textarea class="form-control cookie-category-description-input" rows="2" placeholder="Explain what this category does."></textarea></div></div><div class="mt-4"><div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"><h6 class="mb-0 fw-bold">Cookie Detail Rows</h6><button type="button" class="btn btn-outline-primary btn-sm cookie-detail-add"><i class="fa fa-plus me-1"></i>Add Detail Row</button></div><div class="cookie-detail-list"></div></div></div>`;
        return wrap;
    };

    const syncCategories = () => {
        const categories = [];
        editor.querySelectorAll('.cookie-category-card').forEach((card, index) => {
            const titleInput = card.querySelector('.cookie-category-title-input');
            const keyInput = card.querySelector('.cookie-category-key-input');
            const essentialInput = card.querySelector('.cookie-category-essential');
            const defaultInput = card.querySelector('.cookie-category-default-selected');
            const title = titleInput?.value?.trim() || '';
            const key = slugify(keyInput?.value || title || `cookie_${index + 1}`);
            const isEssential = essentialInput?.checked || false;

            if (defaultInput) {
                if (isEssential) {
                    defaultInput.checked = true;
                    defaultInput.disabled = true;
                } else {
                    defaultInput.disabled = false;
                }
            }

            card.querySelector('.cookie-category-title').textContent = title || 'Cookie Category';
            if (keyInput) { keyInput.value = key; }

            const details = [];
            card.querySelectorAll('.cookie-detail-card').forEach((detail) => {
                const row = {
                    label: detail.querySelector('.cookie-detail-label')?.value?.trim() || '',
                    value: detail.querySelector('.cookie-detail-value')?.value?.trim() || '',
                    link_text: detail.querySelector('.cookie-detail-link-text')?.value?.trim() || '',
                    link_url: detail.querySelector('.cookie-detail-link-url')?.value?.trim() || '',
                };
                if (row.label || row.value || row.link_text || row.link_url) {
                    details.push(row);
                }
            });

            categories.push({
                id: card.dataset.cookieCategoryId || `cookie_${index + 1}`,
                key,
                title: title || `Cookie Category ${index + 1}`,
                description: card.querySelector('.cookie-category-description-input')?.value?.trim() || '',
                is_essential: isEssential,
                default_selected: isEssential || (defaultInput?.checked || false),
                details,
            });
        });

        hiddenInput.value = JSON.stringify(categories);
        if (emptyState) {
            emptyState.classList.toggle('d-none', categories.length > 0);
        }
    };

    addButton?.addEventListener('click', () => {
        editor.appendChild(createCategoryCard());
        syncCategories();
    });

    editor.addEventListener('click', (event) => {
        const addDetail = event.target.closest('.cookie-detail-add');
        if (addDetail) {
            addDetail.closest('.cookie-category-card')?.querySelector('.cookie-detail-list')?.appendChild(createDetailRow());
            syncCategories();
            return;
        }

        const removeDetail = event.target.closest('.cookie-detail-remove');
        if (removeDetail) {
            removeDetail.closest('.cookie-detail-card')?.remove();
            syncCategories();
            return;
        }

        const removeCategory = event.target.closest('.cookie-category-remove');
        if (removeCategory) {
            removeCategory.closest('.cookie-category-item')?.remove();
            syncCategories();
        }
    });

    editor.addEventListener('input', (event) => {
        if (event.target.classList.contains('cookie-category-title-input')) {
            const card = event.target.closest('.cookie-category-card');
            const keyInput = card?.querySelector('.cookie-category-key-input');
            if (keyInput && !keyInput.value.trim()) {
                keyInput.value = slugify(event.target.value);
            }
        }
        syncCategories();
    });

    editor.addEventListener('change', syncCategories);
    syncCategories();
})();
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
(function () {
    const textarea = document.getElementById('cookieBannerDescriptionEditor');
    if (!textarea || !window.ClassicEditor) { return; }

    const preview = document.getElementById('cookieBannerDescriptionPreview');
    const updatePreview = (value) => {
        if (preview) {
            preview.innerHTML = value || '';
        }
    };

    ClassicEditor
        .create(textarea)
        .then((editor) => {
            textarea.ckeditorInstance = editor;
            updatePreview(editor.getData());

            editor.model.document.on('change:data', () => {
                const data = editor.getData();
                textarea.value = data;
                updatePreview(data);
            });
        })
        .catch((error) => console.error('Cookie description editor init failed', error));

    textarea.closest('form')?.addEventListener('submit', () => {
        if (textarea.ckeditorInstance) {
            textarea.value = textarea.ckeditorInstance.getData();
        }
    });
})();
</script>
