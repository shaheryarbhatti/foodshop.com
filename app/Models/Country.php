<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'country_code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
