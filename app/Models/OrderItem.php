<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'serial_number', 'title', 
        'quantity', 'unit_price', 'line_total', 'addition_amount', 'addition_tax', 'addons', 'remarks'
    ];

    protected $casts = [
        'addons' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
