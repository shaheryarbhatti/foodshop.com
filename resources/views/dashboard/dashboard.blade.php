@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>{{ __('Dashboard') }}</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}"><i data-feather="home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('Dashboard') }}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-1">{{ __('Welcome Back') }}</h4>
                                <p class="text-muted mb-0">{{ __('Recent user activity and portal analytics are shown here.') }}</p>
                            </div>
                            <span class="badge bg-primary">{{ now()->format('d M Y') }}</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">{{ __('Recent Logins') }}</div>
                                    <div class="fs-4 fw-bold">{{ count($recentActivity ?? []) }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">{{ __('Today') }}</div>
                                    <div class="fs-4 fw-bold">{{ number_format($visitToday ?? 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">{{ __('Recent User Activity') }}</h5>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Last Login') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivity as $activity)
                                        <tr>
                                            <td class="fw-semibold">{{ $activity->name }}</td>
                                            <td>{{ optional($activity->roles->first())->name ?? '-' }}</td>
                                            <td>{{ optional($activity->last_login_at)->format('d M Y h:i A') ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">{{ __('No recent activity found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($showVisitAnalytics)
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="GET" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Start Date') }}</label>
                                    <input type="date" name="visit_start" class="form-control" value="{{ $visitRangeStart }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('End Date') }}</label>
                                    <input type="date" name="visit_end" class="form-control" value="{{ $visitRangeEnd }}">
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">{{ __('Filter') }}</button>
                                    <a href="{{ route('home') }}" class="btn btn-outline-secondary flex-fill">{{ __('Reset') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="mb-1">{{ __('Portal Visits') }}</h5>
                                    <p class="text-muted mb-0">{{ $visitRangeLabel ?? __('Last 30 Days') }}</p>
                                </div>
                                <span class="badge bg-primary">{{ number_format($visitTotal ?? 0) }}</span>
                            </div>
                            <div id="portalVisitsChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Visitor Summary') }}</h5>
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="text-muted small">{{ __('Total Visits') }}</div>
                                <div class="fs-4 fw-bold">{{ number_format($visitTotal ?? 0) }}</div>
                            </div>
                            <div class="border rounded-3 p-3">
                                <div class="text-muted small">{{ __('Unique IPs') }}</div>
                                <div class="fs-4 fw-bold">{{ number_format($uniqueVisitors ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Visitor Countries') }}</h5>
                            <div id="portalCountriesChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Top Locations') }}</h5>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Country') }}</th>
                                            <th class="text-end">{{ __('Visits') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($countryLabels as $index => $country)
                                            <tr>
                                                <td>{{ $country }}</td>
                                                <td class="text-end">{{ number_format($countryCounts[$index] ?? 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted py-4">{{ __('No visitor data yet.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('load', () => {
        const visitLabels = @json($visitLabels ?? []);
        const visitCounts = @json($visitCounts ?? []);
        const countryLabels = @json($countryLabels ?? []);
        const countryCounts = @json($countryCounts ?? []);

        const renderChart = (selector, options) => {
            const el = document.querySelector(selector);
            if (!el || typeof ApexCharts === 'undefined') {
                return;
            }

            const chart = new ApexCharts(el, options);
            chart.render();
        };

        if (visitLabels.length) {
            renderChart('#portalVisitsChart', {
                chart: { type: 'area', height: 300, toolbar: { show: false } },
                series: [{ name: 'Visits', data: visitCounts }],
                xaxis: { categories: visitLabels },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                colors: ['#2563eb'],
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.4, opacityTo: 0.06, stops: [0, 90, 100] }
                }
            });
        }

        if (countryLabels.length) {
            renderChart('#portalCountriesChart', {
                chart: { type: 'donut', height: 300 },
                series: countryCounts,
                labels: countryLabels,
                legend: { position: 'bottom' },
                dataLabels: { enabled: true }
            });
        }
    });
</script>
@endpush
