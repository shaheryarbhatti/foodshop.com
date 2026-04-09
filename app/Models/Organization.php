<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'location_id',
        'address',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
