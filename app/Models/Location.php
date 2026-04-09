<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $fillable = [
        'name',
        'region_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
