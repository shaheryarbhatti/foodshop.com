<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exchange_rate' => 'decimal:6',
    ];

    // Scope for active currency
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
