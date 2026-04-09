<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAddonValue extends Model
{
    protected $fillable = [
        'product_addon_id',
        'title',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function addon(): BelongsTo
    {
        return $this->belongsTo(ProductAddon::class, 'product_addon_id');
    }
}
