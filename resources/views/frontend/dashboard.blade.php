@extends('layouts.frontend')

@section('title', 'Customer Dashboard')

@php
    $activeNav = 'dashboard';
    $currencyCode = strtoupper((string) ($recentOrders->first()?->payment_currency ?: 'USD'));
    $money = fn ($amount) => $currencyCode . ' ' . number_format((float) $amount, 2);
    $customerImage = $customer->image ? asset('public/storage/' . $customer->image) : null;
@endphp

@section('styles')
<style>
.customer-dashboard{padding:36px 0 56px}
.dashboard-shell{display:grid;grid-template-columns:300px minmax(0,1fr);gap:24px}
.dashboard-sidebar,.dashboard-panel{border-radius:28px;background:rgba(255,255,255,.96);border:1px solid rgba(15,23,42,.08);box-shadow:0 26px 54px rgba(15,23,42,.12)}
.dashboard-sidebar{padding:24px;position:sticky;top:24px;height:fit-content}
.dashboard-avatar{width:74px;height:74px;border-radius:22px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary));color:#fff;font-size:1.8rem;box-shadow:0 18px 34px rgba(15,23,42,.14)}
.dashboard-avatar img{width:100%;height:100%;object-fit:cover}
.dashboard-kicker{margin:18px 0 4px;color:#64748b;font-size:.84rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.dashboard-name{margin:0;color:#0f172a;font-size:1.65rem;font-weight:900;letter-spacing:-.03em}
.dashboard-email{margin-top:8px;color:#475569;font-weight:700;word-break:break-word}
.dashboard-links{margin-top:22px;display:grid;gap:10px}
.dashboard-link{display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:18px;background:#fff;color:#1f2937;text-decoration:none;font-weight:800;border:1px solid rgba(15,23,42,.08);box-shadow:0 10px 18px rgba(15,23,42,.05)}
.dashboard-link.is-active{background:linear-gradient(135deg,var(--portal-primary),#f08b3f);color:#fff;border-color:transparent}
.dashboard-link i{width:18px;text-align:center}
.dashboard-link--logout{border:0;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;justify-content:flex-start}
.dashboard-panel{padding:28px}
.dashboard-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}
.dashboard-hero h1{margin:0;color:#0f172a;font-size:clamp(2rem,3vw,2.7rem);font-weight:900;letter-spacing:-.04em}
.dashboard-hero p{margin:10px 0 0;color:#64748b;max-width:700px;font-size:1rem;line-height:1.7}
.dashboard-hero-badge{display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border-radius:999px;background:linear-gradient(135deg,rgba(255,191,31,.22),rgba(203,43,29,.12));color:#8d411e;font-size:.9rem;font-weight:900}
.dashboard-cards{margin-top:24px;display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:18px}
.dashboard-card{padding:22px;border-radius:22px;border:1px solid rgba(15,23,42,.08);box-shadow:0 14px 30px rgba(15,23,42,.06);transition:transform 0.2s ease, box-shadow 0.2s ease}
.dashboard-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(15,23,42,.1)}
.dashboard-card:nth-child(1){background:linear-gradient(135deg,#eef2ff,#f8faff)}
.dashboard-card:nth-child(2){background:linear-gradient(135deg,#ecfeff,#f5ffff)}
.dashboard-card:nth-child(3){background:linear-gradient(135deg,#fff7ed,#fffcf7)}
.dashboard-card:nth-child(4){background:linear-gradient(135deg,#f0fdf4,#f7fff9)}
.dashboard-card:nth-child(5){background:linear-gradient(135deg,#fff1f2,#fff9fa)}
.dashboard-card__icon{width:54px;height:54px;border-radius:18px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:1.2rem;color:#fff;background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))}
.dashboard-card__value{color:#0f172a;font-size:clamp(1.4rem, 2.5vw, 1.9rem);font-weight:900;line-height:1.2;word-break:break-word}
.dashboard-card__label{margin-top:8px;color:#64748b;font-weight:800;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.02em}
.dashboard-card--spent .dashboard-card__value{font-size:clamp(1.2rem, 2.2vw, 1.6rem)}
.dashboard-grid{margin-top:26px;display:grid;grid-template-columns:minmax(0,1.35fr) minmax(0,.65fr);gap:24px}
.dashboard-block{padding:24px;border-radius:24px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 16px 32px rgba(15,23,42,.05);overflow:hidden}
.dashboard-block h2{margin:0 0 8px;color:#0f172a;font-size:1.6rem;font-weight:900}
.dashboard-block p{margin:0 0 18px;color:#64748b;line-height:1.6}
.dashboard-table{width:100%;border-collapse:collapse}
.dashboard-table th,.dashboard-table td{padding:14px 12px;border-bottom:1px solid rgba(15,23,42,.08);text-align:left;vertical-align:middle}
.dashboard-table th{color:#8d411e;font-size:.83rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em;background:#fffaf5}
.dashboard-table tr:last-child td{border-bottom:0}
.dashboard-order-ref{color:#0f172a;font-weight:900}
.dashboard-order-date,.dashboard-order-total{color:#475569;font-weight:700}
.dashboard-status{display:inline-flex;align-items:center;justify-content:center;min-width:108px;padding:8px 12px;border-radius:999px;font-size:.82rem;font-weight:900;text-transform:capitalize}
.dashboard-status--pending_payment{background:#fff7d6;color:#a16207}
.dashboard-status--processing{background:#e0f2fe;color:#0369a1}
.dashboard-status--shipped{background:#dbeafe;color:#1d4ed8}
.dashboard-status--delivered{background:#dcfce7;color:#15803d}
.dashboard-status--cancelled,.dashboard-status--returned,.dashboard-status--failed{background:#fee2e2;color:#b91c1c}
.dashboard-cta{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:#0f172a;color:#fff;text-decoration:none}
.dashboard-highlight{padding:22px;border-radius:22px;background:linear-gradient(135deg,#101828,#1f2937);color:#fff}
.dashboard-highlight h3{margin:0 0 8px;font-size:1.3rem;font-weight:900}
.dashboard-highlight p{margin:0;color:rgba(255,255,255,.78);line-height:1.7}
.dashboard-highlight-actions{margin-top:18px}
.dashboard-highlight-btn{display:inline-flex;align-items:center;gap:10px;padding:12px 18px;border-radius:999px;background:linear-gradient(135deg,var(--portal-primary),#f08b3f);color:#fff;text-decoration:none;font-weight:900;box-shadow:0 14px 26px rgba(15,23,42,.12)}
.dashboard-meta-list{margin-top:18px;display:grid;gap:14px}
.dashboard-meta-item{padding:16px 18px;border-radius:18px;background:#f8fafc;border:1px solid rgba(15,23,42,.06)}
.dashboard-meta-item span{display:block;color:#64748b;font-size:.78rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase;margin-bottom:6px}
.dashboard-meta-item strong{color:#0f172a;font-size:1rem;display:block;word-break:break-word}
.dashboard-empty{padding:32px 22px;border-radius:20px;border:1px dashed rgba(15,23,42,.16);background:#f8fafc;color:#64748b;text-align:center;font-weight:800}
@media (max-width:1199.98px){
    .dashboard-shell{grid-template-columns:260px minmax(0,1fr);gap:20px}
    .dashboard-cards{grid-template-columns:repeat(3,minmax(0,1fr))}
    .dashboard-grid{grid-template-columns:1fr}
}
@media (max-width:991.98px){
    .dashboard-shell{grid-template-columns:1fr}
    .dashboard-sidebar{position:static;margin-bottom:24px}
    .dashboard-cards{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (max-width:767.98px){
    .customer-dashboard{padding:16px 0 32px}
    .dashboard-panel,.dashboard-sidebar{padding:18px;border-radius:20px}
    .dashboard-hero{flex-direction:column;gap:14px}
     .dashboard-hero h1{font-size:1.8rem}
     .dashboard-hero-badge{padding:10px 14px;align-self:flex-start}
    .dashboard-cards{grid-template-columns:1fr;gap:14px}
    .dashboard-card{padding:18px}
    .dashboard-card__icon{width:48px;height:48px;margin-bottom:12px}
    .dashboard-card__value{font-size:1.5rem}
    .dashboard-grid{gap:20px}
    .dashboard-block{padding:18px;border-radius:20px}
    .dashboard-block h2{font-size:1.4rem}
    .dashboard-table{display:table;width:100%}
    .table-responsive{margin: 0 -18px; padding: 0 18px; overflow-x: auto; -webkit-overflow-scrolling: touch}
    .dashboard-table th,.dashboard-table td{padding:12px 10px;font-size:0.85rem}
    .dashboard-status{min-width:auto;padding:6px 10px;font-size:0.75rem}
}
@media (max-width:480px){
    .dashboard-cards{grid-template-columns:1fr}
    .dashboard-hero h1{font-size:1.5rem}
}
</style>
@endsection

@section('content')
<section class="customer-dashboard">
    <div class="container">
        <div class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="dashboard-avatar">
                    @if($customerImage)
                        <img src="{{ $customerImage }}" alt="Profile">
                    @else
                        <i class="fa-solid fa-user"></i>
                    @endif
                </div>
                <div class="dashboard-kicker">Customer Portal</div>
                <h2 class="dashboard-name">{{ $customer->full_name }}</h2>
                <div class="dashboard-email">{{ $customer->email }}</div>

                <div class="dashboard-links">
                    <a href="{{ route('frontend.dashboard') }}" class="dashboard-link is-active">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('frontend.profile') }}" class="dashboard-link">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Profile</span>
                    </a>
                    <form method="POST" action="{{ route('frontend.logout') }}">
                        @csrf
                        <button type="submit" class="dashboard-link dashboard-link--logout w-100">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="dashboard-panel">
                <div class="dashboard-hero">
                    <div>
                        <h1>Dashboard</h1>
                        <p>Track your recent orders, check progress at a glance, and manage your customer account from one polished dashboard.</p>
                    </div>
                    <div class="dashboard-hero-badge">
                        <i class="fa-solid fa-sparkles"></i>
                        <span>{{ $recentOrders->count() }} recent orders ready to review</span>
                    </div>
                </div>

                <div class="dashboard-cards">
                    <div class="dashboard-card">
                        <div class="dashboard-card__icon"><i class="fa-solid fa-bag-shopping"></i></div>
                        <div class="dashboard-card__value">{{ $stats['total_orders'] }}</div>
                        <div class="dashboard-card__label">Total Orders</div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card__icon"><i class="fa-solid fa-hourglass-half"></i></div>
                        <div class="dashboard-card__value">{{ $stats['pending_orders'] }}</div>
                        <div class="dashboard-card__label">Pending Orders</div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card__icon"><i class="fa-solid fa-truck-fast"></i></div>
                        <div class="dashboard-card__value">{{ $stats['active_orders'] }}</div>
                        <div class="dashboard-card__label">Active Orders</div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card__icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="dashboard-card__value">{{ $stats['completed_orders'] }}</div>
                        <div class="dashboard-card__label">Completed Orders</div>
                    </div>
                    <div class="dashboard-card dashboard-card--spent">
                        <div class="dashboard-card__icon"><i class="fa-solid fa-wallet"></i></div>
                        <div class="dashboard-card__value">{{ $money($stats['total_spent']) }}</div>
                        <div class="dashboard-card__label">Total Spent</div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-block">
                        <h2>Recent Orders</h2>
                        <p>Your latest order activity is listed here so you can keep an eye on progress without leaving the dashboard.</p>

                        @if($recentOrders->isEmpty())
                            <div class="dashboard-empty">
                                No orders yet. Start exploring the menu and place your first order.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>#Order</th>
                                            <th>Date</th>
                                            <th>Order Total</th>
                                            <th>Order Status</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentOrders as $order)
                                            <tr>
                                                <td class="dashboard-order-ref">{{ $order->order_number ?: ('#' . $order->id) }}</td>
                                                <td class="dashboard-order-date">{{ optional($order->created_at)->format('d M Y') ?: '-' }}</td>
                                                <td class="dashboard-order-total">{{ strtoupper((string) ($order->payment_currency ?: 'USD')) }} {{ number_format((float) $order->grand_total, 2) }}</td>
                                                <td>
                                                    <span class="dashboard-status dashboard-status--{{ $order->order_status }}">
                                                        {{ \Illuminate\Support\Str::headline($order->order_status ?: 'pending') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('frontend.dashboard.order', $order) }}" class="dashboard-cta" aria-label="View order">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="dashboard-block">
                        <div class="dashboard-highlight">
                            <h3>Delivery Profile</h3>
                            <p>Your saved customer details help us route orders faster and keep checkout smooth on every visit.</p>
                            <div class="dashboard-highlight-actions">
                                <a href="{{ route('frontend.profile') }}" class="dashboard-highlight-btn">
                                    <i class="fa-solid fa-user-pen"></i>
                                    <span>Edit Profile</span>
                                </a>
                            </div>
                        </div>

                        <div class="dashboard-meta-list">
                            <div class="dashboard-meta-item">
                                <span>Phone</span>
                                <strong>{{ $customer->phone ?: '-' }}</strong>
                            </div>
                            <div class="dashboard-meta-item">
                                <span>Address</span>
                                <strong>{{ trim(($customer->address ?: '-') . (filled($customer->city) ? ', ' . $customer->city : '') . (filled($customer->country) ? ', ' . $customer->country : '')) }}</strong>
                            </div>
                            <div class="dashboard-meta-item">
                                <span>Postal Code</span>
                                <strong>{{ $customer->postal_code ?: '-' }}</strong>
                            </div>
                            <div class="dashboard-meta-item">
                                <span>Customer Type</span>
                                <strong>{{ ucfirst($customer->customer_type ?: 'individual') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
