<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $order->order_number ?: ('#' . $order->id) }} - {{ __('invoice') }}</title>
    @php
        $invoiceTheme = \App\Models\Setting::getMany(['primary_color' => '#7367f0', 'secondary_color' => '#f73164']);
        $themePrimary = $invoiceTheme['primary_color'] ?: '#7367f0';
        $themeSecondary = $invoiceTheme['secondary_color'] ?: '#f73164';
        $fullName = trim($order->first_name . ' ' . $order->last_name);
        $statusClass = in_array($order->payment_status, ['paid', 'success'], true) ? 'status-pill--success' : 'status-pill--warning';
        $currency = strtoupper((string) ($order->payment_currency ?: 'USD'));
        $money = fn ($amount) => $currency . ' ' . number_format((float) $amount, 2);
    @endphp
    <style>
        :root{--ink:#0f172a;--muted:#64748b;--line:#dbe4f0;--panel:#ffffff;--soft:#f8fafc;--brand:{{ $themePrimary }};--brand-secondary:{{ $themeSecondary }};--success:#16a34a;--warning:#d97706}*{box-sizing:border-box}body{margin:0;padding:32px 0;background:linear-gradient(180deg,color-mix(in srgb,var(--brand) 8%,#ffffff),color-mix(in srgb,var(--brand-secondary) 10%,#f8fbff));color:var(--ink);font-family:Arial,Helvetica,sans-serif}.invoice-page{max-width:1180px;margin:0 auto;padding:0 18px}.invoice-shell{background:var(--panel);border-radius:30px;overflow:hidden;box-shadow:0 30px 70px rgba(15,23,42,.12)}.invoice-hero{display:flex;justify-content:space-between;gap:20px;padding:34px 38px;background:linear-gradient(135deg,var(--brand),var(--brand-secondary));color:#fff}.invoice-hero__eyebrow{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.12);font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin-bottom:14px}.invoice-hero h1{margin:0 0 8px;font-size:38px;line-height:1.05}.invoice-hero p{margin:0;color:rgba(255,255,255,.8);max-width:520px;line-height:1.6}.invoice-hero__meta{min-width:280px;padding:20px 22px;border-radius:24px;background:rgba(255,255,255,.08)}.invoice-hero__meta div+div{margin-top:15px}.invoice-hero__meta span{display:block;color:rgba(255,255,255,.7);font-size:12px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:5px}.invoice-hero__meta strong{font-size:18px}.invoice-body{padding:34px 38px 38px}.invoice-actions{display:flex;justify-content:flex-end;gap:12px;margin-bottom:24px}.invoice-action{display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border-radius:999px;text-decoration:none;font-weight:700;border:1px solid transparent}.invoice-action--primary{background:var(--brand);color:#fff}.invoice-action--light{background:var(--soft);color:var(--ink);border-color:var(--line)}.invoice-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:24px}.invoice-card{padding:24px;border-radius:24px;background:#fff;border:1px solid var(--line);box-shadow:0 16px 30px rgba(15,23,42,.04)}.invoice-card h3{margin:0 0 16px;font-size:20px}.invoice-identity{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.invoice-label{display:block;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px}.invoice-value{font-size:16px;line-height:1.6;font-weight:700}.invoice-value--normal{font-weight:500}.status-pill{display:inline-flex;align-items:center;padding:9px 14px;border-radius:999px;font-size:13px;font-weight:800;letter-spacing:.02em}.status-pill--success{background:rgba(22,163,74,.12);color:var(--success)}.status-pill--warning{background:rgba(217,119,6,.13);color:var(--warning)}.invoice-items-table{width:100%;border-collapse:collapse}.invoice-items-table th,.invoice-items-table td{padding:16px 14px;border-bottom:1px solid #e8eef6;text-align:left;vertical-align:top}.invoice-items-table th{color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.1em;background:color-mix(in srgb,var(--brand) 8%,#ffffff)}.invoice-items-table tr:last-child td{border-bottom:0}.item-title{font-size:16px;font-weight:700;margin-bottom:6px}.item-meta{color:var(--muted);font-size:13px;line-height:1.6}.invoice-summary{display:flex;flex-direction:column;gap:12px;margin-top:8px}.invoice-summary__row{display:flex;justify-content:space-between;gap:16px;font-size:15px;color:#334155}.invoice-summary__row strong{color:var(--ink)}.invoice-summary__total{margin-top:8px;padding-top:16px;border-top:1px solid var(--line);font-size:21px;font-weight:800}.invoice-footer{margin-top:24px;padding:22px 24px;border-radius:22px;background:linear-gradient(135deg,color-mix(in srgb,var(--brand) 10%,#ffffff),color-mix(in srgb,var(--brand-secondary) 10%,#ffffff));color:#334155;line-height:1.75}@media print{body{background:#fff;padding:0}.invoice-page{max-width:none;padding:0}.invoice-shell{box-shadow:none;border-radius:0}.invoice-actions{display:none}}@media (max-width:991px){.invoice-grid,.invoice-identity{grid-template-columns:1fr}.invoice-hero{flex-direction:column}}
    </style>
</head>
<body>
<div class="invoice-page">
    <div class="invoice-shell">
        <div class="invoice-hero">
            <div>
                <div class="invoice-hero__eyebrow">{{ __('invoice') }}</div>
                <h1>{{ $order->order_number ?: ('#' . $order->id) }}</h1>
                <p>{{ __('invoice_professional_note') }}</p>
            </div>
            <div class="invoice-hero__meta">
                <div><span>{{ __('invoice_date') }}</span><strong>{{ optional($order->created_at)->format('d M Y, h:i A') ?: '-' }}</strong></div>
                <div><span>{{ __('payment_method') }}</span><strong>{{ $paymentMethodTitle }}</strong></div>
                <div><span>{{ __('payment_status') }}</span><strong class="status-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $order->payment_status ?: 'pending')) }}</strong></div>
            </div>
        </div>
        <div class="invoice-body">
            <div class="invoice-actions">
                <a href="{{ route('frontend.staff.orders.invoice.download', $order) }}" class="invoice-action invoice-action--primary">{{ __('download_pdf_invoice') }}</a>
                @if($googleMapsRouteUrl)
                    <a href="{{ $googleMapsRouteUrl }}" target="_blank" rel="noopener" class="invoice-action invoice-action--light">{{ __('open_in_google_maps') }}</a>
                @endif
            </div>
            <div class="invoice-grid">
                <div class="invoice-card">
                    <h3>{{ __('billing_details') }}</h3>
                    <div class="invoice-identity">
                        <div><span class="invoice-label">{{ __('customer') }}</span><div class="invoice-value">{{ $fullName ?: '-' }}</div></div>
                        <div><span class="invoice-label">{{ __('company_name') }}</span><div class="invoice-value invoice-value--normal">{{ $order->company_name ?: '-' }}</div></div>
                        <div><span class="invoice-label">{{ __('email') }}</span><div class="invoice-value invoice-value--normal">{{ $order->email ?: '-' }}</div></div>
                        <div><span class="invoice-label">{{ __('phone') }}</span><div class="invoice-value invoice-value--normal">{{ $order->phone ?: '-' }}</div></div>
                        <div style="grid-column:1 / -1;"><span class="invoice-label">{{ __('address') }}</span><div class="invoice-value invoice-value--normal">{{ trim($order->address . ', ' . $order->city . ', ' . $order->country) ?: '-' }}</div></div>
                    </div>
                </div>
                <div class="invoice-card">
                    <h3>{{ __('order_summary') }}</h3>
                    <div class="invoice-identity">
                        <div><span class="invoice-label">{{ __('branch') }}</span><div class="invoice-value invoice-value--normal">{{ optional($order->organization)->name ?: '-' }}</div></div>
                        <div><span class="invoice-label">{{ __('order_type') }}</span><div class="invoice-value invoice-value--normal">{{ $order->order_type === 'pick_up' ? __('frontend_pick_up') : __('frontend_delivery') }}</div></div>
                        <div><span class="invoice-label">{{ __('delivery_distance') }}</span><div class="invoice-value invoice-value--normal">{{ $order->delivery_distance_km ? number_format((float) $order->delivery_distance_km, 2) . ' km' : '-' }}</div></div>
                        <div><span class="invoice-label">{{ __('order_status') }}</span><div class="invoice-value invoice-value--normal">{{ \Illuminate\Support\Str::headline($order->order_status ?: '-') }}</div></div>
                        <div style="grid-column:1 / -1;"><span class="invoice-label">{{ __('order_notes') }}</span><div class="invoice-value invoice-value--normal">{{ $order->order_notes ?: __('not_available') }}</div></div>
                    </div>
                </div>
            </div>
            <div class="invoice-card" style="margin-top:24px;">
                <h3>{{ __('ordered_items') }}</h3>
                <table class="invoice-items-table">
                    <thead><tr><th>{{ __('item') }}</th><th>{{ __('quantity') }}</th><th>{{ __('unit_price') }}</th><th>{{ __('total') }}</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td><div class="item-title">{{ $item->title }}</div></td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $money($item->unit_price) }}</td>
                                <td>{{ $money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="invoice-grid" style="margin-top:24px;">
                <div class="invoice-card">
                    <h3>{{ __('delivery_information') }}</h3>
                    <div class="invoice-identity">
                        <div><span class="invoice-label">{{ __('branch_coordinates') }}</span><div class="invoice-value invoice-value--normal">{{ optional($order->organization)->latitude && optional($order->organization)->longitude ? optional($order->organization)->latitude . ', ' . optional($order->organization)->longitude : '-' }}</div></div>
                        <div><span class="invoice-label">{{ __('customer_coordinates') }}</span><div class="invoice-value invoice-value--normal">{{ $order->customer_latitude && $order->customer_longitude ? $order->customer_latitude . ', ' . $order->customer_longitude : '-' }}</div></div>
                    </div>
                </div>
                <div class="invoice-card">
                    <h3>{{ __('payment_breakdown') }}</h3>
                    <div class="invoice-summary">
                        <div class="invoice-summary__row"><span>{{ __('subtotal') }}</span><strong>{{ $money($order->subtotal) }}</strong></div>
                        <div class="invoice-summary__row"><span>{{ __('shipping_fee') }}</span><strong>{{ $money($order->shipping_costs) }}</strong></div>
                        <div class="invoice-summary__row"><span>{{ __('frontend_vat') }}</span><strong>{{ $money($order->vat_amount) }}</strong></div>
                        <div class="invoice-summary__row invoice-summary__total"><span>{{ __('grand_total') }}</span><strong>{{ $money($order->grand_total) }}</strong></div>
                    </div>
                </div>
            </div>
            <div class="invoice-footer"><strong>{{ __('thank_you_for_your_order') }}</strong><br>{{ __('invoice_footer_copy') }}</div>
        </div>
    </div>
</div>
</body>
</html>
