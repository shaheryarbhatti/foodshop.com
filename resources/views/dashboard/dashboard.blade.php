<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Business Dashboard</title>

@extends('layouts.app')

@section('content')
@php
    $rangeButtons = [
        'year' => 'Year',
        'last_month' => 'Last month',
        'current_month' => 'Current month',
        'last_7_days' => 'Last 7 days',
    ];
@endphp

<style>
.admin-dashboard{padding:26px 0 40px}.admin-dashboard .page-title{margin-bottom:22px}.dashboard-filter-card,.dashboard-metric-card,.dashboard-panel{background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:24px;box-shadow:0 16px 40px rgba(15,23,42,.06)}.dashboard-filter-card{padding:16px 18px}.dashboard-filter-row{display:flex;flex-wrap:wrap;gap:14px;align-items:center}.dashboard-filter-buttons{display:flex;flex-wrap:wrap;gap:10px;flex:1 1 520px}.dashboard-filter-button{display:inline-flex;align-items:center;gap:10px;padding:13px 18px;border-radius:14px;text-decoration:none;border:1px solid rgba(115,103,240,.12);border-color:color-mix(in srgb,var(--theme-default,#7367f0) 16%,#ffffff);background:#fff;color:var(--theme-default,#7367f0);font-weight:700;transition:.2s ease}.dashboard-filter-button.is-active{background:linear-gradient(135deg,var(--theme-default,#7367f0),var(--theme-secondary,#8b5cf6));color:#fff;box-shadow:0 14px 28px color-mix(in srgb,var(--theme-default,#7367f0) 28%,transparent)}.dashboard-filter-dates{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:flex-end;flex:1 1 420px}.dashboard-filter-dates input{min-width:170px;height:50px;border-radius:14px;border:1px solid rgba(148,163,184,.22);background:#f8fafc;padding:0 14px}.dashboard-filter-submit{height:50px;padding:0 24px;border:0;border-radius:14px;background:linear-gradient(135deg,var(--theme-default,#7367f0),var(--theme-secondary,#8b5cf6));color:#fff;font-weight:800}.dashboard-caption{margin-top:14px;color:#64748b;font-size:.95rem}.dashboard-metric-grid{margin-top:22px}.dashboard-metric-card{padding:20px;min-height:140px;display:flex;gap:18px;align-items:center;overflow:hidden}.dashboard-metric-card>div:last-child{min-width:0;flex:1}.metric-icon-wrap{width:76px;height:76px;border-radius:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0}.metric-icon-wrap i{font-size:1.65rem;color:var(--theme-default,#7367f0)}.dashboard-metric-card.is-violet .metric-icon-wrap{background:linear-gradient(135deg,color-mix(in srgb,var(--theme-default,#7367f0) 16%,#ffffff),color-mix(in srgb,var(--theme-secondary,#8b5cf6) 12%,#ffffff))}.dashboard-metric-card.is-amber .metric-icon-wrap{background:linear-gradient(135deg,#fff7df,#ffefc8)}.dashboard-metric-card.is-sky .metric-icon-wrap{background:linear-gradient(135deg,#e6f6ff,#d9f1ff)}.dashboard-metric-card.is-mint .metric-icon-wrap{background:linear-gradient(135deg,#e6faf4,#dcf7ef)}.dashboard-metric-card.is-cyan .metric-icon-wrap{background:linear-gradient(135deg,#e4fbfb,#d6f7f7)}.dashboard-metric-card.is-pink .metric-icon-wrap{background:linear-gradient(135deg,#fbe8f2,#f7ddeb)}.dashboard-metric-card.is-orange .metric-icon-wrap{background:linear-gradient(135deg,#fff0e5,#ffe2ca)}.dashboard-metric-card.is-lime .metric-icon-wrap{background:linear-gradient(135deg,#edf9e2,#e3f4d1)}.metric-label{color:#475569;font-size:.96rem;line-height:1.42;margin-bottom:8px;max-width:18ch}.metric-value{font-size:clamp(1.55rem,1.05rem + 1vw,2.05rem);font-weight:900;color:#0f172a;letter-spacing:-.04em;line-height:1.08;word-break:break-word;overflow-wrap:anywhere}.dashboard-grid{margin-top:24px}.dashboard-panel{padding:24px;height:100%}.panel-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;flex-wrap:wrap;margin-bottom:18px}.panel-head h4{margin:0;font-size:1.3rem;font-weight:900;color:#0f172a}.panel-head p{margin:6px 0 0;color:#64748b;max-width:720px}.panel-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:color-mix(in srgb,var(--theme-default,#7367f0) 10%,#ffffff);color:var(--theme-default,#7367f0);font-weight:800;font-size:.82rem}.dashboard-chart{min-height:340px}.dashboard-chart--tall{min-height:380px}.dashboard-chart--small{min-height:300px}.dashboard-mini-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:20px}.dashboard-mini-stat{padding:16px 18px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.14)}.dashboard-mini-stat span{display:block;color:#64748b;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px}.dashboard-mini-stat strong{font-size:1.2rem;color:#0f172a}.dashboard-table{margin:0}.dashboard-table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:#64748b;border-bottom-color:rgba(148,163,184,.18)}.dashboard-table td{vertical-align:middle;color:#0f172a}.dashboard-list{display:grid;gap:12px}.dashboard-list-item{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:15px 16px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.14)}.dashboard-list-item strong{display:block;font-size:1rem;color:#0f172a}.dashboard-list-item span{display:block;margin-top:4px;color:#64748b}.dashboard-list-item b{font-size:1.1rem;color:var(--theme-default,#7367f0)}.chart-empty{min-height:300px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#f8fafc;color:#94a3b8;font-weight:700;border:1px dashed rgba(148,163,184,.22)}@media (max-width:1199.98px){.metric-value{font-size:1.55rem}.dashboard-mini-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:767.98px){.admin-dashboard{padding:18px 0 32px}.dashboard-filter-card,.dashboard-metric-card,.dashboard-panel{border-radius:18px}.dashboard-filter-buttons,.dashboard-filter-dates,.dashboard-mini-stats{grid-template-columns:1fr}.dashboard-filter-button,.dashboard-filter-submit,.dashboard-filter-dates input{width:100%}.dashboard-metric-card{padding:18px;align-items:flex-start}.metric-icon-wrap{width:64px;height:64px}.metric-label{max-width:none}.metric-value{font-size:1.4rem}.dashboard-mini-stats{display:grid;grid-template-columns:1fr}.dashboard-chart,.dashboard-chart--tall,.dashboard-chart--small{min-height:280px}}
</style>

<div class="page-body admin-dashboard">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Business Dashboard</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}"><i data-feather="home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="dashboard-filter-card">
            <form method="GET" class="dashboard-filter-row">
                <div class="dashboard-filter-buttons">
                    @foreach($rangeButtons as $value => $label)
                        <a href="{{ route('home', ['range' => $value]) }}" class="dashboard-filter-button {{ $rangeKey === $value ? 'is-active' : '' }}">
                            <i class="fa-regular fa-clock"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="dashboard-filter-dates">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="from_date" value="{{ request('from_date', optional($startDate)->format('Y-m-d')) }}" placeholder="From date">
                    <input type="date" name="until_date" value="{{ request('until_date', optional($endDate)->format('Y-m-d')) }}" placeholder="Until date">
                    <button type="submit" class="dashboard-filter-submit">Go <i class="fa-solid fa-arrow-right ms-1"></i></button>
                </div>
            </form>
            <div class="dashboard-caption">
                <strong>{{ $rangeLabel }}</strong>:
                {{ optional($startDate)->format('d M Y') }} to {{ optional($endDate)->format('d M Y') }}.
                The cards and charts below update automatically for the selected period.
            </div>
        </div>

        <div class="row g-4 dashboard-metric-grid">
            @foreach($summaryCards as $card)
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="dashboard-metric-card {{ $card['theme'] }}">
                        <div class="metric-icon-wrap">
                            <i class="fa-solid {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="metric-label">{{ $card['title'] }}</div>
                            <div class="metric-value">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 dashboard-grid">
            <div class="col-12">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Revenue and Order Flow</h4>
                            <p>The main timeline compares revenue and incoming orders across the selected period so you can spot growth, drops, and busy days instantly.</p>
                        </div>
                        <span class="panel-badge">{{ $rangeDays }} day period</span>
                    </div>
                    <div id="revenueOrdersChart" class="dashboard-chart dashboard-chart--tall"></div>
                    <div class="dashboard-mini-stats">
                        <div class="dashboard-mini-stat">
                            <span>Average Order Value</span>
                            <strong>{{ $averageOrderValueFormatted }}</strong>
                        </div>
                        <div class="dashboard-mini-stat">
                            <span>Paid Orders</span>
                            <strong>{{ number_format($paidOrders) }}</strong>
                        </div>
                        <div class="dashboard-mini-stat">
                            <span>Active Branches</span>
                            <strong>{{ number_format($activeBranches) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Order Status Breakdown</h4>
                            <p>See how orders are distributed across each status in the selected date range.</p>
                        </div>
                    </div>
                    <div id="orderStatusChart" class="dashboard-chart dashboard-chart--small"></div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Payment Method Mix</h4>
                            <p>Track which payment methods customers prefer during this period.</p>
                        </div>
                    </div>
                    <div id="paymentMethodChart" class="dashboard-chart dashboard-chart--small"></div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Branch Revenue Performance</h4>
                            <p>Branches are ranked by revenue so you can quickly compare the strongest locations.</p>
                        </div>
                    </div>
                    <div id="branchPerformanceChart" class="dashboard-chart"></div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Top Selling Products</h4>
                            <p>The products below are leading by sold quantity within the selected range.</p>
                        </div>
                    </div>
                    <div id="topProductsChart" class="dashboard-chart"></div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Delivery vs Pickup Trend</h4>
                            <p>Compare operational load between delivery orders and pickups over time.</p>
                        </div>
                    </div>
                    <div id="orderTypeTrendChart" class="dashboard-chart"></div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Customer Growth</h4>
                            <p>New customer signups are plotted against your selected timeline to show acquisition momentum.</p>
                        </div>
                    </div>
                    <div id="customerGrowthChart" class="dashboard-chart"></div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Top Customers This Period</h4>
                            <p>Your highest-value customers based on total spend in the selected range.</p>
                        </div>
                    </div>
                    <div class="dashboard-list">
                        @forelse($topCustomers as $customer)
                            <div class="dashboard-list-item">
                                <div>
                                    <strong>{{ $customer->customer_name ?: 'Walk-in Customer' }}</strong>
                                    <span>{{ number_format($customer->total_orders) }} orders</span>
                                </div>
                                <b>{{ \App\Services\CurrencyService::formatAmount((float) $customer->total_spend) }}</b>
                            </div>
                        @empty
                            <div class="chart-empty">No customer spend data found for this range.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="dashboard-panel">
                    <div class="panel-head">
                        <div>
                            <h4>Recent User Activity</h4>
                            <p>Latest admin-side user activity helps you keep an eye on team access and engagement.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table dashboard-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
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
                                        <td colspan="3" class="text-center text-muted py-4">No recent activity found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($showVisitAnalytics)
                <div class="col-xl-6">
                    <div class="dashboard-panel">
                        <div class="panel-head">
                            <div>
                                <h4>Portal Visits</h4>
                                <p>Traffic coming into the portal is shown here for extra business context.</p>
                            </div>
                            <span class="panel-badge">{{ number_format($visitTotal) }} visits</span>
                        </div>
                        <div id="portalVisitsChart" class="dashboard-chart"></div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="dashboard-panel">
                        <div class="panel-head">
                            <div>
                                <h4>Visitor Countries</h4>
                                <p>Visitor geography helps you understand where portal traffic is coming from.</p>
                            </div>
                        </div>
                        <div id="portalCountriesChart" class="dashboard-chart"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', function () {
    if (typeof ApexCharts === 'undefined') {
        return;
    }

    const css = getComputedStyle(document.documentElement);
    const themeColor = (css.getPropertyValue('--theme-default') || '#7367f0').trim();
    const themeColorSoft = (css.getPropertyValue('--theme-secondary') || themeColor).trim();
    const muted = '#64748b';
    const border = 'rgba(148, 163, 184, 0.18)';

    const labels = @json($timelineLabels);
    const revenueSeries = @json($revenueSeries);
    const ordersSeries = @json($ordersSeries);
    const customerSeries = @json($customerSeries);
    const deliverySeries = @json($deliverySeries);
    const pickupSeries = @json($pickupSeries);
    const statusLabels = @json($statusChartLabels);
    const statusSeries = @json($statusChartSeries);
    const paymentLabels = @json($paymentLabels);
    const paymentSeries = @json($paymentSeries);
    const branchLabels = @json($branchLabels);
    const branchRevenueSeries = @json($branchRevenueSeries);
    const branchOrderSeries = @json($branchOrderSeries);
    const productLabels = @json($productLabels);
    const productQtySeries = @json($productQtySeries);
    const productSalesSeries = @json($productSalesSeries);
    const visitLabels = @json($visitLabels ?? []);
    const visitCounts = @json($visitCounts ?? []);
    const countryLabels = @json($countryLabels ?? []);
    const countryCounts = @json($countryCounts ?? []);

    const renderChart = function (selector, options, hasSeries = true) {
        const el = document.querySelector(selector);
        if (!el) {
            return;
        }

        if (hasSeries && Array.isArray(options.series) && options.series.length === 0) {
            el.innerHTML = '<div class="chart-empty">No data available for this range.</div>';
            return;
        }

        if (hasSeries && Array.isArray(options.series) && options.series.every(item => {
            if (Array.isArray(item)) return item.length === 0;
            if (Array.isArray(item?.data)) return item.data.length === 0;
            return false;
        })) {
            el.innerHTML = '<div class="chart-empty">No data available for this range.</div>';
            return;
        }

        new ApexCharts(el, options).render();
    };

    const common = {
        chart: {
            toolbar: { show: false },
            fontFamily: 'inherit',
            foreColor: muted,
        },
        grid: {
            borderColor: border,
            strokeDashArray: 4,
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            labels: { colors: muted },
        },
        xaxis: {
            labels: { style: { colors: muted } },
            axisBorder: { color: border },
            axisTicks: { color: border },
        },
        yaxis: {
            labels: { style: { colors: muted } },
        },
        tooltip: {
            theme: 'light',
        },
    };

    renderChart('#revenueOrdersChart', {
        ...common,
        chart: { ...common.chart, height: 380, type: 'line' },
        colors: [themeColor, '#06b6d4'],
        series: [
            { name: 'Revenue', type: 'area', data: revenueSeries },
            { name: 'Orders', type: 'line', data: ordersSeries },
        ],
        stroke: { curve: 'smooth', width: [3, 3] },
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.04,
                stops: [0, 90, 100]
            }
        },
        xaxis: { ...common.xaxis, categories: labels },
    });

    renderChart('#orderStatusChart', {
        chart: { type: 'donut', height: 300 },
        series: statusSeries,
        labels: statusLabels,
        legend: { position: 'bottom' },
        colors: ['#f59e0b', '#06b6d4', '#2563eb', '#16a34a', '#64748b', '#0f172a', '#ef4444'],
        stroke: { colors: ['#ffffff'] },
        dataLabels: { enabled: true },
    });

    renderChart('#paymentMethodChart', {
        ...common,
        chart: { ...common.chart, type: 'bar', height: 300 },
        series: [{ name: 'Orders', data: paymentSeries }],
        xaxis: { ...common.xaxis, categories: paymentLabels },
        colors: [themeColor],
        plotOptions: {
            bar: {
                borderRadius: 10,
                columnWidth: '48%',
            }
        }
    });

    renderChart('#branchPerformanceChart', {
        ...common,
        chart: { ...common.chart, type: 'bar', height: 340 },
        series: [
            { name: 'Revenue', data: branchRevenueSeries },
            { name: 'Orders', data: branchOrderSeries },
        ],
        colors: [themeColor, themeColorSoft],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 8,
                barHeight: '58%',
            }
        },
        xaxis: { ...common.xaxis, categories: branchLabels },
    });

    renderChart('#topProductsChart', {
        ...common,
        chart: { ...common.chart, type: 'bar', height: 340 },
        series: [
            { name: 'Qty Sold', data: productQtySeries },
            { name: 'Sales', data: productSalesSeries },
        ],
        colors: ['#ec4899', '#f97316'],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 8,
                barHeight: '58%',
            }
        },
        xaxis: { ...common.xaxis, categories: productLabels },
    });

    renderChart('#orderTypeTrendChart', {
        ...common,
        chart: { ...common.chart, type: 'bar', stacked: true, height: 320 },
        series: [
            { name: 'Delivery', data: deliverySeries },
            { name: 'Pickup', data: pickupSeries },
        ],
        colors: ['#2563eb', '#10b981'],
        xaxis: { ...common.xaxis, categories: labels },
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '48%',
            }
        }
    });

    renderChart('#customerGrowthChart', {
        ...common,
        chart: { ...common.chart, type: 'area', height: 320 },
        series: [{ name: 'New Customers', data: customerSeries }],
        colors: ['#0ea5e9'],
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.32,
                opacityTo: 0.04,
                stops: [0, 90, 100]
            }
        },
        xaxis: { ...common.xaxis, categories: labels },
    });

    if (visitLabels.length) {
        renderChart('#portalVisitsChart', {
            ...common,
            chart: { ...common.chart, type: 'line', height: 320 },
            series: [{ name: 'Visits', data: visitCounts }],
            colors: ['#22c55e'],
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { ...common.xaxis, categories: visitLabels },
        });
    }

    if (countryLabels.length) {
        renderChart('#portalCountriesChart', {
            chart: { type: 'donut', height: 320 },
            series: countryCounts,
            labels: countryLabels,
            legend: { position: 'bottom' },
            colors: [themeColor, themeColorSoft, '#22c55e', '#f59e0b', '#ef4444', '#0f172a', '#94a3b8'],
        });
    }
});
</script>
@endpush
