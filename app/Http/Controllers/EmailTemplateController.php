<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTemplateController extends Controller
{
    private function templates(): array
    {
        return [
            'account_welcome' => [
                'subject' => 'Account Welcome Details',
                'body' => "Hi {name},\n\nYour account has been created successfully.\n\nEmail: {email}\nPassword: {password}\nLogin URL: {login_url}\n\nThanks,\n{brand_name}",
            ],
            'account_deactivation' => [
                'subject' => 'Account Deactivation',
                'body' => "Hi {name},\n\nYour account has been deactivated. If you think this is a mistake, please contact us.\n\nThanks,\n{brand_name}",
            ],
            'order_status_pending_payment' => [
                'subject' => 'Order {order_number} - Waiting for Payment',
                'body' => "Hi {name},\n\nYour order {order_number} is currently waiting for payment.\n\nTotal: {grand_total}\n\nPlease complete your payment using the link below:\n{payment_link}\n\nThanks,\n{brand_name}",
            ],
            'order_status_processing' => [
                'subject' => 'Order {order_number} - Processing',
                'body' => "Hi {name},\n\nYour order {order_number} is now being processed.\n\nTotal: {grand_total}\n\nThanks,\n{brand_name}",
            ],
            'order_status_shipped' => [
                'subject' => 'Order {order_number} - Shipped',
                'body' => "Hi {name},\n\nGreat news! Your order {order_number} has been shipped.\n\nThanks,\n{brand_name}",
            ],
            'order_status_delivered' => [
                'subject' => 'Order {order_number} - Delivered',
                'body' => "Hi {name},\n\nYour order {order_number} has been successfully delivered. Enjoy your meal!\n\nThanks,\n{brand_name}",
            ],
            'order_status_cancelled' => [
                'subject' => 'Order {order_number} - Cancelled',
                'body' => "Hi {name},\n\nYour order {order_number} has been cancelled.\n\nIf you have any questions, please contact us.\n\nThanks,\n{brand_name}",
            ],
            'order_status_returned' => [
                'subject' => 'Order {order_number} - Returned',
                'body' => "Hi {name},\n\nYour order {order_number} has been marked as returned.\n\nThanks,\n{brand_name}",
            ],
            'order_status_failed' => [
                'subject' => 'Order {order_number} - Failed',
                'body' => "Hi {name},\n\nUnfortunately, your order {order_number} has failed to process correctly.\n\nPlease contact our support team for assistance.\n\nThanks,\n{brand_name}",
            ],
            'order_balance_payment' => [
                'subject' => 'Payment Required: Update for Order {order_number}',
                'body' => "Hi {name},\n\nYour order {order_number} has been updated. An additional payment is required to complete the order.\n\nBalance Due: {grand_total}\n\nPlease pay the balance here:\n{payment_link}\n\nThanks,\n{brand_name}",
            ],
            'order_invoice_link' => [
                'subject' => 'Invoice for Order {order_number}',
                'body' => "Hi {name},\n\nPlease find your invoice for order {order_number} at the link below:\n\n{invoice_link}\n\nThanks,\n{brand_name}",
            ],
        ];
    }

    public function manage()
    {
        $templates = [];
        foreach ($this->templates() as $key => $defaults) {
            $templates[$key] = EmailTemplate::firstOrCreate(['key' => $key], $defaults);
        }

        return view('emails.email_template', compact('templates'));
    }

    public function update(Request $request)
    {
        $payload = $request->except(['_token']);
        
        foreach ($this->templates() as $key => $defaults) {
            $subject = $payload[$key . '_subject'] ?? null;
            $body = $payload[$key . '_body'] ?? null;
            
            if ($subject !== null || $body !== null) {
                EmailTemplate::updateOrCreate(
                    ['key' => $key],
                    [
                        'subject' => $subject ?? $defaults['subject'],
                        'body' => $body ?? $defaults['body']
                    ]
                );
            }
        }

        return redirect()->route('email.manage')->with('success', 'Email templates updated successfully.');
    }

    public function sendTest(Request $request)
    {
        $allKeys = array_keys($this->templates());
        $validated = $request->validate([
            'template_key' => 'required|in:' . implode(',', $allKeys),
            'test_name' => 'required|string|max:255',
            'test_email' => 'required|email|max:255',
            'test_password' => 'nullable|string|max:255',
        ]);

        $template = EmailTemplate::where('key', $validated['template_key'])->first();
        if (!$template) {
            return redirect()->route('email.manage')->with('error', 'Template not found.');
        }

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

        $brandName = Setting::get('smtp_from_name', config('app.name', 'Food Shop'));
        $data = [
            'userName' => $validated['test_name'],
            'name' => $validated['test_name'],
            'email' => $validated['test_email'],
            'password' => $validated['test_password'] ?? 'Temp@123',
            'loginUrl' => url('/login'),
            'brandName' => $brandName,
            'order_number' => 'ORD-' . date('Ymd') . '-000001',
            'grand_total' => 'USD 100.00',
            'payment_link' => url('/pay/test'),
            'invoice_link' => url('/invoice/test'),
            'status' => 'Processing'
        ];

        $replacedBody = strtr((string) ($template->body ?? ''), [
            '{name}' => $data['name'],
            '{email}' => $data['email'],
            '{password}' => $data['password'],
            '{unique_id}' => 'test-id-123',
            '{login_url}' => $data['loginUrl'],
            '{brand_name}' => $data['brandName'],
            '{order_number}' => $data['order_number'],
            '{grand_total}' => $data['grand_total'],
            '{payment_link}' => $data['payment_link'],
            '{invoice_link}' => $data['invoice_link'],
            '{status}' => $data['status'],
        ]);

        // Decide which view to use. For now, use a generic one if it's order related.
        $view = 'emails.account_welcome';
        if (str_starts_with($validated['template_key'], 'order_')) {
            $view = 'emails.order_status_update';
        } elseif ($validated['template_key'] === 'account_deactivation') {
            $view = 'emails.account_deactivation';
        }

        try {
            Mail::send($view, array_merge($data, ['customBody' => $replacedBody]), function ($message) use ($validated, $template) {
                $message->to($validated['test_email'])->subject($template->subject ?? 'Test Email');
            });
        } catch (\Throwable $e) {
            return redirect()->route('email.manage')->with('error', 'Failed to send test email: ' . $e->getMessage());
        }

        return redirect()->route('email.manage')->with('success', 'Test email sent successfully.');
    }
}
