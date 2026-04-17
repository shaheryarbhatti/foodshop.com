<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_FAILED = 'failed';
    public const DRIVER_OFFER_PENDING = 'pending';
    public const DRIVER_OFFER_OFFERED = 'offered';
    public const DRIVER_OFFER_ACCEPTED = 'accepted';
    public const DRIVER_OFFER_REJECTED = 'rejected';
    public const DRIVER_OFFER_STOPPED = 'stopped';

    protected $fillable = [
        'order_number', 'customer_id', 'organization_id', 'shipping_fee_id', 'coupon_id', 'delivery_distance_km', 'customer_latitude', 'customer_longitude',
        'driver_id',
        'driver_offer_status', 'driver_offer_driver_id', 'driver_offer_attempts', 'driver_offer_sent_at', 'driver_offer_responded_at',
        'coupon_code',
        'first_name', 'last_name', 'company_name', 'email', 'phone', 
        'address', 'city', 'postal_code', 'country', 'different_delivery_address',
        'order_type', 'order_notes', 'payment_method', 'payment_gateway', 'payment_status', 'order_status',
        'payment_reference', 'payment_currency', 'payment_payload',
        'subtotal', 'shipping_costs', 'vat_amount', 'discount_amount', 'grand_total',
        'amount_paid', 'extra_amount_paid', 'admin_updated'
    ];

    protected $casts = [
        'different_delivery_address' => 'boolean',
        'payment_payload' => 'array',
        'delivery_distance_km' => 'decimal:2',
        'customer_latitude' => 'decimal:7',
        'customer_longitude' => 'decimal:7',
        'admin_updated' => 'boolean',
        'extra_amount_paid' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'driver_offer_attempts' => 'integer',
        'driver_offer_sent_at' => 'datetime',
        'driver_offer_responded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (blank($order->order_status)) {
                $order->order_status = static::resolveInitialStatus(
                    $order->payment_method,
                    $order->payment_status
                );
            }
        });

        static::created(function (Order $order) {
            if (blank($order->order_number)) {
                $order->forceFill([
                    'order_number' => static::generateOrderNumber($order),
                ])->saveQuietly();
            }
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('order_status')) {
                \App\Services\OrderEmailService::sendStatusEmail($order);
            }
        });
    }

    public static function statusOptions(): array
    {
        return [
            static::STATUS_PENDING_PAYMENT => __('pending_payment'),
            static::STATUS_PROCESSING => __('processing'),
            static::STATUS_SHIPPED => __('shipped'),
            static::STATUS_DELIVERED => __('delivered'),
            static::STATUS_CANCELLED => __('cancelled'),
            static::STATUS_RETURNED => __('returned'),
            static::STATUS_FAILED => __('failed'),
        ];
    }

    public static function resolveInitialStatus(?string $paymentMethod, ?string $paymentStatus): string
    {
        if ($paymentMethod === 'bank_account') {
            return static::STATUS_PENDING_PAYMENT;
        }

        if ($paymentMethod === 'cash_on_delivery') {
            return static::STATUS_PROCESSING;
        }

        if ($paymentStatus === 'paid') {
            return static::STATUS_PROCESSING;
        }

        return static::STATUS_PENDING_PAYMENT;
    }

    public function requiresOfflinePaymentConfirmationForDelivery(): bool
    {
        return in_array($this->payment_method, ['cash_on_delivery', 'bank_account'], true)
            && ! in_array($this->payment_status, ['paid', 'success'], true);
    }

    public function canBeMarkedPaidManually(): bool
    {
        return in_array($this->payment_method, ['cash_on_delivery', 'bank_account'], true)
            && ! in_array($this->payment_status, ['paid', 'success'], true);
    }

    public function isFinalizedForDriverDispatch(): bool
    {
        return in_array($this->order_status, [
            static::STATUS_DELIVERED,
            static::STATUS_CANCELLED,
            static::STATUS_RETURNED,
            static::STATUS_FAILED,
        ], true);
    }

    public function canAutoOfferToDrivers(): bool
    {
        return ! $this->driver_id
            && ! $this->isFinalizedForDriverDispatch()
            && filled($this->organization_id);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function shippingFee(): BelongsTo
    {
        return $this->belongsTo(ShippingFee::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function offeredDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_offer_driver_id');
    }

    private static function generateOrderNumber(Order $order): string
    {
        $datePart = optional($order->created_at)->format('Ymd') ?: now()->format('Ymd');

        return 'ORD-' . $datePart . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }
}
