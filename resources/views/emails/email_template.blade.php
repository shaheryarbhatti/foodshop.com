<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('email_templates') }}</title>

    @extends('layouts.app')
    @section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="row"><div class="col-12"><div class="card" style="margin-top: 20px;">
                <div class="card-header pb-0"><h4>{{ __('email_templates') }}</h4><p class="text-muted mb-0">{{ __('email_templates_desc') }}</p></div>
                <form action="{{ route('email.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Template Selection (Sidebar) -->
                            <div class="col-md-3">
                                <div class="nav flex-column nav-pills border-end pe-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3 small" style="letter-spacing: 1px;">{{ __('standard_emails') }}</h6>
                                    @php $firstKey = array_key_first($templates); @endphp
                                    @foreach($templates as $key => $template)
                                        @if(!str_starts_with($key, 'order_'))
                                            <button class="nav-link text-start mb-1 {{ $key === $firstKey ? 'active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $key }}" type="button" role="tab">
                                                <i class="fa {{ $key === 'account_welcome' ? 'fa-user-plus' : 'fa-user-minus' }} me-2"></i>
                                                {{ __(str_replace('order_status_', '', $key)) }}
                                            </button>
                                        @endif
                                    @endforeach

                                    <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3 small" style="letter-spacing: 1px;">{{ __('order_emails') }}</h6>
                                    @foreach($templates as $key => $template)
                                        @if(str_contains($key, 'order_'))
                                            <button class="nav-link text-start mb-1 {{ $key === $firstKey ? 'active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $key }}" type="button" role="tab">
                                                <i class="fa fa-shopping-cart me-2"></i>
                                                {{ __(str_replace('order_status_', '', $key)) }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Template Editor Content -->
                            <div class="col-md-9">
                                <!-- Test Email Section (Compact) -->
                                <div class="bg-light rounded-3 p-3 mb-4 border">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold m-0"><i class="fa fa-paper-plane me-2 text-primary"></i>{{ __('send_test_email') }}</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="sendTestOfActive()">
                                            <i class="fa fa-envelope me-1"></i> {{ __('send_test_for_active_tab') }}
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0"><i class="fa fa-user text-muted"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0" id="test_name" placeholder="{{ __('name') }}" value="Test User">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0"><i class="fa fa-at text-muted"></i></span>
                                                <input type="email" class="form-control border-start-0 ps-0" id="test_email" placeholder="{{ __('email') }}" value="test@example.com">
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 11px;">{{ __('send_test_email_note') }}</small>
                                </div>

                                <div class="tab-content" id="v-pills-tabContent">
                                    @php $firstKey = array_key_first($templates); @endphp
                                    @foreach($templates as $key => $template)
                                        <div class="tab-pane fade {{ $key === $firstKey ? 'show active' : '' }}" id="{{ $key }}" role="tabpanel">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold d-flex justify-content-between">
                                                    {{ __('subject') }}
                                                    <span class="badge bg-secondary-light text-secondary fw-normal">{{ $key }}</span>
                                                </label>
                                                <input type="text" name="{{ $key }}_subject" class="form-control form-control-lg border-shadow" value="{{ old($key . '_subject', $template->subject) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('body') }}</label>
                                                <textarea name="{{ $key }}_body" class="form-control border-shadow" rows="12" style="font-family: 'Courier New', Courier, monospace; font-size: 14px;">{{ old($key . '_body', $template->body) }}</textarea>
                                            </div>

                                            <div class="alert alert-info border-0 shadow-sm mt-4 py-3">
                                                <div class="d-flex">
                                                    <div class="me-3"><i class="fa fa-info-circle fa-2x text-info"></i></div>
                                                    <div>
                                                        <strong class="d-block mb-1">{{ __('available_placeholders') }}</strong>
                                                        <div class="text-dark small opacity-75">
                                                            @php
                                                                $placeholders = ['{name}', '{email}', '{login_url}', '{brand_name}'];
                                                                if(str_contains($key, 'order')) {
                                                                    $placeholders = array_merge($placeholders, ['{order_number}', '{grand_total}', '{status}', '{payment_link}', '{invoice_link}']);
                                                                }
                                                                if($key === 'account_welcome') $placeholders[] = '{password}';
                                                            @endphp
                                                            @foreach($placeholders as $p)
                                                                <code class="bg-white px-2 py-1 rounded border me-1 mb-1 d-inline-block">{{ $p }}</code>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end bg-white border-top-0 pt-0 pb-4 pe-4">
                        <button type="submit" class="btn btn-success px-4 shadow">
                            <i class="fa fa-save me-2"></i>{{ __('save_templates') }}
                        </button>
                    </div>
                </form>

                <form id="emailTestForm" method="POST" action="{{ route('email.test') }}">
                    @csrf
                    <input type="hidden" name="template_key" id="test_template_key">
                    <input type="hidden" name="test_name" id="test_name_hidden">
                    <input type="hidden" name="test_email" id="test_email_hidden">
                </form>
            </div></div></div>
        </div>
    </div>

    <style>
        .nav-pills .nav-link { color: #64748b; font-weight: 500; border-radius: 8px; transition: all 0.2s; }
        .nav-pills .nav-link:hover { background-color: #f1f5f9; color: #334155; }
        .nav-pills .nav-link.active { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
        .border-shadow { box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-color: #e2e8f0; }
        .border-shadow:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .bg-secondary-light { background-color: #f8fafc; }
    </style>
    @endsection

    @push('scripts')
    <script>
        const testForm = document.getElementById('emailTestForm');
        const templateKeyInput = document.getElementById('test_template_key');
        const testNameInput = document.getElementById('test_name');
        const testEmailInput = document.getElementById('test_email');
        const hiddenNameInput = document.getElementById('test_name_hidden');
        const hiddenEmailInput = document.getElementById('test_email_hidden');

        const sendTestOfActive = () => {
            const activeTabButton = document.querySelector('#v-pills-tab .nav-link.active');
            if (activeTabButton) {
                const templateKey = activeTabButton.id.replace('-tab', '');
                sendTest(templateKey);
            }
        };

        const sendTest = (templateKey) => {
            const testName = testNameInput.value.trim();
            const testEmail = testEmailInput.value.trim();
            if (!testName || !testEmail) {
                alert('Please enter a name and email first.');
                return;
            }
            templateKeyInput.value = templateKey;
            hiddenNameInput.value = testName;
            hiddenEmailInput.value = testEmail;
            testForm.submit();
        };
    </script>
    @endpush

