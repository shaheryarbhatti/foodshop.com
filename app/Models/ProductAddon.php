<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAddon extends Model
{
    protected $fillable = [
        'title',
        'selection_type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_addon_product', 'product_addon_id', 'product_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAddonValue::class)->orderBy('title');
    }
}
