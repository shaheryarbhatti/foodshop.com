<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $order->order_number ?: ('#' . $order->id) }}</title>
    @php
        $invoiceTheme = \App\Models\Setting::getMany([
            'primary_color' => '#7367f0',
            'secondary_color' => '#f73164',
        ]);
        $themePrimary = $invoiceTheme['primary_color'] ?: '#7367f0';
        $themeSecondary = $invoiceTheme['secondary_color'] ?: '#f73164';
    @endphp
    <style>
        body { margin:0; color:#1e293b; font-family:DejaVu Sans, sans-serif; font-size:10px; line-height:1.38; }
        .page { padding:18px 22px; }
        .hero { padding:16px 18px; border-radius:14px; background:{{ $themePrimary }}; background-image: linear-gradient(135deg, {{ $themePrimary }}, {{ $themeSecondary }}); color:#fff; }
        .hero table,.info-grid,.summary,table.items { width:100%; border-collapse:collapse; }
        .hero h1 { margin:0 0 5px; font-size:21px; }
        .hero p { margin:0; color:rgba(255,255,255,.82); font-size:10px; }
        .hero-meta { text-align:right; }
        .label { display:block; font-size:8px; color:#64748b; text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px; }
        .hero .label { color:rgba(255,255,255,.72); }
        .value { font-size:10px; color:#0f172a; font-weight:700; margin-bottom:8px; }
        .hero-meta .value { font-size:11px; color:#fff; }
        .value.normal { font-weight:500; }
        .section { margin-top:14px; border:1px solid #dbe4f0; border-radius:12px; overflow:hidden; }
        .section-head { padding:9px 12px; background:#f8fafc; font-size:11px; font-weight:700; color:{{ $themePrimary }}; }
        .section-body { padding:12px; }
        .info-grid td { width:25%; vertical-align:top; padding:0 8px 0 0; }
        table.items th,table.items td { border-bottom:1px solid #e2e8f0; padding:8px 7px; text-align:left; vertical-align:top; }
        table.items th { background:#f8fafc; color:#64748b; font-size:8px; text-transform:uppercase; letter-spacing:.08em; }
        .summary td { padding:4px 0; }
        .summary tr.total td { padding-top:8px; border-top:1px solid #cbd5e1; font-size:12px; font-weight:800; color:#0f172a; }
        .footer { margin-top:14px; padding:10px 12px; border-radius:12px; background:#f8fafc; color:#475569; font-size:9px; }
        .muted { color:#64748b; }
        .item-compact { font-size:9px; line-height:1.35; }
    </style>
</head>
<body>
@php
    $fullName = trim($order->first_name . ' ' . $order->last_name);
    $currency = strtoupper((string) ($order->payment_currency ?: 'USD'));
    $money = fn ($amount) => $currency . ' ' . number_format((float) $amount, 2);
@endphp
<div class="page">
    <div class="hero">
        <table>
            <tr>
                <td>
                    <h1>{{ __('invoice') }}</h1>
                    <p>{{ __('invoice_professional_note') }}</p>
                </td>
                <td class="hero-meta">
                    <span class="label">{{ __('order_reference') }}</span>
                    <div class="value">{{ $order->order_number ?: ('#' . $order->id) }}</div>
                    <span class="label">{{ __('invoice_date') }}</span>
                    <div class="value">{{ optional($order->created_at)->format('d M Y, h:i A') ?: '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-head">{{ __('order_summary') }}</div>
        <div class="section-body">
            <table class="info-grid">
                <tr>
                    <td>
                        <span class="label">{{ __('customer') }}</span>
                        <div class="value normal">{{ $fullName ?: '-' }}</div>
                    </td>
                    <td>
                        <span class="label">{{ __('contact') }}</span>
                        <div class="value normal">{{ $order->email ?: '-' }}<br>{{ $order->phone ?: '-' }}</div>
                    </td>
                    <td>
                        <span class="label">{{ __('branch') }}</span>
                        <div class="value normal">{{ optional($order->organization)->name ?: '-' }}</div>
                    </td>
                    <td>
                        <span class="label">{{ __('payment_method') }}</span>
                        <div class="value normal">{{ $paymentMethodTitle }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="label">{{ __('address') }}</span>
                        <div class="value normal">{{ trim($order->address . ', ' . $order->city . ', ' . $order->country) ?: '-' }}</div>
                    </td>
                    <td>
                        <span class="label">{{ __('order_type') }}</span>
                        <div class="value normal">{{ $order->order_type === 'pick_up' ? __('frontend_pick_up') : __('frontend_delivery') }}</div>
                    </td>
                    <td>
                        <span class="label">{{ __('payment_status') }}</span>
                        <div class="value normal">{{ ucfirst(str_replace('_', ' ', $order->payment_status ?: 'pending')) }}</div>
                    </td>
                    <td>
                        <span class="label">{{ __('order_status') }}</span>
                        <div class="value normal">{{ \Illuminate\Support\Str::headline($order->order_status ?: '-') }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-head">{{ __('ordered_items') }}</div>
        <div class="section-body" style="padding:0;">
            <table class="items">
                <thead>
                    <tr>
                        <th>{{ __('item') }}</th>
                        <th>{{ __('quantity') }}</th>
                        <th>{{ __('unit_price') }}</th>
                        <th>{{ __('total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->title }}</strong>
                                <div class="muted item-compact">
                                    @if($item->serial_number)
                                        {{ __('serial_number') }}: {{ $item->serial_number }}<br>
                                    @endif
                                    @if(is_array($item->addons) && count($item->addons))
                                        {{ __('addons') }}:
                                        {{ collect($item->addons)->map(function ($addon) {
                                            if (is_array($addon)) {
                                                return $addon['title'] ?? $addon['name'] ?? ($addon['label'] ?? null);
                                            }
                                            return $addon;
                                        })->filter()->implode(', ') }}<br>
                                    @endif
                                    @if($item->remarks)
                                        {{ __('remarks') }}: {{ $item->remarks }}
                                    @endif
                                </div>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $money($item->unit_price) }}</td>
                            <td>{{ $money($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <table class="info-grid" style="margin-top:14px;">
        <tr>
            <td style="width:55%; padding-right:10px;">
                <div class="footer">
                    <strong>{{ __('thank_you_for_your_order') }}</strong><br>
                    {{ __('invoice_footer_copy') }}
                    @if($order->order_notes)
                        <br><br><strong>{{ __('order_notes') }}:</strong> {{ $order->order_notes }}
                    @endif
                </div>
            </td>
            <td style="width:45%;">
                <div class="section">
                    <div class="section-head">{{ __('payment_breakdown') }}</div>
                    <div class="section-body">
                        <table class="summary">
                            <tr><td>{{ __('subtotal') }}</td><td style="text-align:right;">{{ $money($order->subtotal) }}</td></tr>
                            <tr><td>{{ __('shipping_fee') }}</td><td style="text-align:right;">{{ $money($order->shipping_costs) }}</td></tr>
                            <tr><td>{{ __('frontend_vat') }}</td><td style="text-align:right;">{{ $money($order->vat_amount) }}</td></tr>
                            <tr class="total"><td>{{ __('grand_total') }}</td><td style="text-align:right;">{{ $money($order->grand_total) }}</td></tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
