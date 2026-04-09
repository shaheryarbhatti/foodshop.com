<?php

namespace Database\Seeders;

use App\Models\ShippingFee;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class ShippingFeeSeeder extends Seeder
{
    public function run(): void
    {
        $taxId = Tax::where('title', 'VAT')->value('id');

        ShippingFee::updateOrCreate(
            ['title' => 'Local Delivery'],
            [
                'tax_id' => $taxId,
                'fee' => 2.50,
                'condition_type' => 'less_than',
                'distance_value' => '5',
                'maximum_order_amount' => 50,
                'delivery_type' => 'delivery',
                'status' => true,
            ]
        );
    }
}
