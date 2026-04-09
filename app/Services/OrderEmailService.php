<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class OrderEmailService
{
    public static function sendStatusEmail(Order $order, string $templateKey = null)
    {
        if (!$templateKey) {
            $templateKey = 'order_status_' . $order->order_status;
        }

        $template = EmailTemplate::where('key', $templateKey)->first();
        if (!$template) {
            return;
        }

        self::loadSmtpConfig();

        $brandName = Setting::get('smtp_from_name', config('app.name', 'Food Shop'));
        
        $paymentLink = route('frontend.orders.pay-balance', [
            'order' => $order->id,
            'signature' => hash_hmac('sha256', (string)$order->id, config('app.key'))
        ]);
        
        $invoiceLink = route('orders.invoice.download', $order->id); // Note: Admin route, might need a frontend one or signed one if public

        $data = [
            'name' => $order->first_name . ' ' . $order->last_name,
            'email' => $order->email,
            'order_number' => $order->order_number,
            'grand_total' => strtoupper((string)($order->payment_currency ?: 'USD')) . ' ' . number_format($order->grand_total, 2),
            'status' => Order::statusOptions()[$order->order_status] ?? $order->order_status,
            'payment_link' => $paymentLink,
            'invoice_link' => $invoiceLink,
            'brandName' => $brandName
        ];

        $replacedBody = strtr((string) ($template->body ?? ''), [
            '{name}' => $data['name'],
            '{email}' => $data['email'],
            '{order_number}' => $data['order_number'],
            '{grand_total}' => $data['grand_total'],
            '{status}' => $data['status'],
            '{payment_link}' => $data['payment_link'],
            '{invoice_link}' => $data['invoice_link'],
            '{brand_name}' => $data['brandName'],
        ]);

        try {
            Mail::send('emails.order_status_update', array_merge($data, ['customBody' => $replacedBody]), function ($message) use ($order, $template) {
                $message->to($order->email)->subject($template->subject ?? 'Order Update');
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to send order status email: ' . $e->getMessage());
        }
    }

    private static function loadSmtpConfig()
    {
        $smtpHost = Setting::get('smtp_host');
        if ($smtpHost) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtpHost,
                'mail.mailers.smtp.port' => Setting::get('smtp_port', 587),
                'mail.mailers.smtp.encryption' => Setting::get('smtp_encryption', 'tls') ?: null,
                'mail.mailers.smtp.username' => Setting::get('smtp_username'),
                'mail.mailers.smtp.password' => Setting::get('smtp_password'),
                'mail.from.address' => Setting::get('smtp_from_email', config('mail.from.address')),
                'mail.from.name' => Setting::get('smtp_from_name', config('mail.from.name')),
            ]);
        }
    }
}
