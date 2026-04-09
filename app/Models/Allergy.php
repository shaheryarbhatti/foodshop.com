<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergy extends Model
{
    protected $fillable = ['title', 'code', 'status'];

    /**
     * Get the products associated with this allergy.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_allergy');
    }
}
