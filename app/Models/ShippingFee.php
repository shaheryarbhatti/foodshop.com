<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingFee extends Model
{
    protected $fillable = [
        'title',
        'tax_id',
        'fee',
        'min_order_amount',
        'condition_type',
        'distance_value',
        'maximum_order_amount',
        'delivery_type',
        'status',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'maximum_order_amount' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
