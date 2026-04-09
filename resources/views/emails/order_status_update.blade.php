<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Update</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f5f7; font-family: Arial, sans-serif; }
    </style>
</head>
<body>
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f4f5f7; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="480" style="max-width:480px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 12px 30px rgba(15,23,42,0.12);">
                    <tr>
                        <td align="center" style="background:#10b981; padding:32px 20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="84" height="84" style="border:6px solid rgba(255,255,255,0.6); border-radius:50%; width:84px; height:84px;">
                                <tr>
                                    <td align="center" valign="middle" style="font-size:44px; line-height:44px; color:#ffffff; font-family: Arial, sans-serif;">
                                        <i class="fa fa-shopping-basket"></i>
                                        ORD
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 10px; text-align:center;">
                            <h2 style="margin:0 0 10px; font-size:22px; color:#2d2d2d;">Order Update</h2>
                            <p style="margin:0; color:#6b7280; font-size:14px; line-height:1.6; white-space:pre-line;">{!! nl2br(e($customBody)) !!}</p>
                        </td>
                    </tr>
                    
                    @if(isset($payment_link))
                    <tr>
                        <td align="center" style="padding:22px 28px 10px;">
                            <a href="{{ $payment_link }}" style="background:#2563eb; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:10px; display:inline-block; font-weight:700; font-size:14px;">Pay Now</a>
                        </td>
                    </tr>
                    @endif

                    @if(isset($invoice_link))
                    <tr>
                        <td align="center" style="padding:22px 28px 10px;">
                            <a href="{{ $invoice_link }}" style="background:#6366f1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:10px; display:inline-block; font-weight:700; font-size:14px;">View Invoice</a>
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td align="center" style="padding:0 28px 24px;">
                            <p style="margin:20px 0 0; color:#9ca3af; font-size:12px;">Order Number: {{ $order_number ?? 'N/A' }}</p>
                        </td>
                    </tr>
                </table>
                <div style="margin-top:12px; font-size:12px; color:#9ca3af;">{{ $brandName }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
