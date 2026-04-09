<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name',
        'country_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
