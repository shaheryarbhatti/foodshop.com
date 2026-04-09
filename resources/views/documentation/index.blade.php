@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="mb-1">{{ __('system_documentation') }}</h3>
                        <p class="text-muted mb-0">{{ __('Documentation for settings, users, and reporting tools.') }}</p>
                    </div>
                    <span class="badge bg-primary">{{ __('quick_guide') }}</span>
                </div>

                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="border rounded-3 p-4 h-100">
                            <h5 class="mb-3">{{ __('settings_module') }}</h5>
                            <p class="mb-2"><strong>{{ __('theme_settings') }}:</strong> {{ __('theme_settings_desc') }}</p>
                            <p class="mb-2"><strong>{{ __('login_page_settings') }}:</strong> {{ __('login_page_settings_desc') }}</p>
                            <p class="mb-2"><strong>{{ __('email_settings') }}:</strong> {{ __('email_settings_desc') }}</p>
                            <p class="mb-0"><strong>{{ __('currency_settings') }}:</strong> {{ __('currency_settings_desc') }}</p>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="border rounded-3 p-4 h-100">
                            <h5 class="mb-3">{{ __('reports_module') }}</h5>
                            <p class="mb-2"><strong>{{ __('visitor_analytics') }}:</strong> {{ __('visitor_analytics_desc') }}</p>
                            <p class="mb-2"><strong>{{ __('Recent User Activity') }}:</strong> {{ __('Use the dashboard to review recent staff access and portal traffic.') }}</p>
                            <p class="mb-0"><strong>{{ __('Dashboard') }}:</strong> {{ __('Use the dashboard to review staff activity and portal traffic.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
