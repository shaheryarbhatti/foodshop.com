<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'customer_type',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'country_id',
        'latitude',
        'longitude',
        'image',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function countryRelation(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
