<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'serial_number',
        'category_id',
        'title',
        'permalink',
        'description',
        'image',
        'base_price',
        'status',
    ];

    protected $casts = [
        'serial_number' => 'integer',
        'base_price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(ProductAddon::class, 'product_addon_product', 'product_id', 'product_addon_id')->orderBy('title');
    }

    /**
     * Get the allergies associated with this product.
     */
    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class, 'product_allergy');
    }
}
