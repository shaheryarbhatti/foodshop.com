<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'calculation_type',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function shippingFees(): HasMany
    {
        return $this->hasMany(ShippingFee::class);
    }
}
