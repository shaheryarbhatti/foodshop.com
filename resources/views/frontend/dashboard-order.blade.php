@extends('layouts.frontend')

@section('title', 'Order Details')

@php
    $activeNav = 'dashboard';
    $currency = strtoupper((string) ($order->payment_currency ?: 'USD'));
    $money = fn ($amount) => $currency . ' ' . number_format((float) $amount, 2);
    $fullName = trim($order->first_name . ' ' . $order->last_name);
@endphp

@section('styles')
<style>
.customer-order-page{padding:36px 0 56px}
.customer-order-shell{max-width:1180px;margin:0 auto;padding:0 12px}
.customer-order-card{border-radius:30px;background:rgba(255,255,255,.97);border:1px solid rgba(15,23,42,.08);box-shadow:0 28px 60px rgba(15,23,42,.14);overflow:hidden}
.customer-order-hero{display:flex;justify-content:space-between;gap:20px;padding:28px 30px;background:linear-gradient(135deg,var(--portal-primary),#f08b3f);color:#fff}
.customer-order-hero h1{margin:0 0 8px;font-size:clamp(2rem,3vw,2.8rem);font-weight:900;letter-spacing:-.04em}
.customer-order-hero p{margin:0;color:rgba(255,255,255,.84);line-height:1.7}
.customer-order-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:999px;background:rgba(255,255,255,.16);font-weight:800}
.customer-order-meta{min-width:280px;padding:18px 20px;border-radius:24px;background:rgba(255,255,255,.12)}
.customer-order-meta span{display:block;color:rgba(255,255,255,.74);font-size:.8rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px}
.customer-order-meta strong{display:block;font-size:1rem;margin-bottom:14px}
.customer-order-body{padding:28px 30px 30px}
.customer-order-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:12px;margin-bottom:22px}
.customer-order-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 18px;border-radius:999px;font-weight:800;text-decoration:none}
.customer-order-btn--primary{background:#0f172a;color:#fff}
.customer-order-btn--light{background:#fff7ed;color:#8d411e;border:1px solid rgba(141,65,30,.14)}
.customer-order-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}
.customer-order-panel{padding:22px;border-radius:24px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 14px 28px rgba(15,23,42,.05)}
.customer-order-panel h2{margin:0 0 16px;font-size:1.35rem;font-weight:900;color:#0f172a}
.customer-order-label{display:block;color:#64748b;font-size:.78rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:5px}
.customer-order-value{color:#0f172a;font-weight:800;line-height:1.7}
.customer-order-items{margin-top:22px}
.customer-order-table{width:100%;border-collapse:collapse}
.customer-order-table th,.customer-order-table td{padding:14px 12px;border-bottom:1px solid rgba(15,23,42,.08);text-align:left;vertical-align:top}
.customer-order-table th{font-size:.8rem;color:#8d411e;background:#fff8f1;text-transform:uppercase;letter-spacing:.08em}
.customer-order-total{margin-top:18px;padding:20px;border-radius:22px;background:linear-gradient(135deg,#fff7ed,#fffdf7);border:1px solid rgba(15,23,42,.06)}
.customer-order-total-row{display:flex;justify-content:space-between;gap:16px;padding:6px 0;color:#334155;font-weight:700}
.customer-order-total-row strong{color:#0f172a}
.customer-order-total-row.is-grand{margin-top:10px;padding-top:14px;border-top:1px solid rgba(15,23,42,.1);font-size:1.1rem;font-weight:900}
@media (max-width:991.98px){.customer-order-hero{flex-direction:column}.customer-order-grid{grid-template-columns:1fr}}
@media (max-width:767.98px){.customer-order-page{padding:24px 0 40px}.customer-order-hero,.customer-order-body{padding:22px 18px}.customer-order-table{display:block;overflow-x:auto;white-space:nowrap}}
</style>
@endsection

@section('content')
<section class="customer-order-page">
    <div class="customer-order-shell">
        <div class="customer-order-card">
            <div class="customer-order-hero">
                <div>
                    <div class="customer-order-pill"><i class="fa-solid fa-receipt"></i><span>Order Details</span></div>
                    <h1>{{ $order->order_number ?: ('#' . $order->id) }}</h1>
                    <p>Review your order summary, delivery details, and payment breakdown in one clean customer-friendly view.</p>
                </div>
                <div class="customer-order-meta">
                    <span>Order Date</span>
                    <strong>{{ optional($order->created_at)->format('d M Y, h:i A') ?: '-' }}</strong>
                    <span>Payment Status</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $order->payment_status ?: 'pending')) }}</strong>
                    <span>Order Status</span>
                    <strong>{{ \Illuminate\Support\Str::headline($order->order_status ?: '-') }}</strong>
                </div>
            </div>

            <div class="customer-order-body">
                <div class="customer-order-actions">
                    <a href="{{ route('frontend.dashboard') }}" class="customer-order-btn customer-order-btn--light">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back To Dashboard</span>
                    </a>
                    <a href="{{ route('frontend.home') }}" class="customer-order-btn customer-order-btn--primary">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Order Again</span>
                    </a>
                </div>

                <div class="customer-order-grid">
                    <div class="customer-order-panel">
                        <h2>Billing Details</h2>
                        <span class="customer-order-label">Customer</span>
                        <div class="customer-order-value">{{ $fullName ?: '-' }}</div>
                        <span class="customer-order-label">Email</span>
                        <div class="customer-order-value">{{ $order->email ?: '-' }}</div>
                        <span class="customer-order-label">Phone</span>
                        <div class="customer-order-value">{{ $order->phone ?: '-' }}</div>
                        <span class="customer-order-label">Address</span>
                        <div class="customer-order-value">{{ trim($order->address . ', ' . $order->city . ', ' . $order->country) ?: '-' }}</div>
                    </div>

                    <div class="customer-order-panel">
                        <h2>Delivery Summary</h2>
                        <span class="customer-order-label">Branch</span>
                        <div class="customer-order-value">{{ optional($order->organization)->name ?: '-' }}</div>
                        <span class="customer-order-label">Order Type</span>
                        <div class="customer-order-value">{{ $order->order_type === 'pick_up' ? __('frontend_pick_up') : __('frontend_delivery') }}</div>
                        <span class="customer-order-label">Distance</span>
                        <div class="customer-order-value">{{ $order->delivery_distance_km ? number_format((float) $order->delivery_distance_km, 2) . ' km' : '-' }}</div>
                        <span class="customer-order-label">Notes</span>
                        <div class="customer-order-value">{{ $order->order_notes ?: '-' }}</div>
                    </div>
                </div>

                <div class="customer-order-panel customer-order-items">
                    <h2>Ordered Items</h2>
                    <div class="table-responsive">
                        <table class="customer-order-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->title }}</strong>
                                            @if($item->remarks)
                                                <div class="text-muted small mt-1">{{ $item->remarks }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $money($item->unit_price) }}</td>
                                        <td>{{ $money($item->line_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="customer-order-total">
                        <div class="customer-order-total-row"><span>Subtotal</span><strong>{{ $money($order->subtotal) }}</strong></div>
                        <div class="customer-order-total-row"><span>Shipping</span><strong>{{ $money($order->shipping_costs) }}</strong></div>
                        <div class="customer-order-total-row"><span>VAT</span><strong>{{ $money($order->vat_amount) }}</strong></div>
                        @if((float) $order->discount_amount > 0)
                            <div class="customer-order-total-row"><span>{{ __('coupon_discount') }}{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span><strong>-{{ $money($order->discount_amount) }}</strong></div>
                        @endif
                        <div class="customer-order-total-row is-grand"><span>Grand Total</span><strong>{{ $money($order->grand_total) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
