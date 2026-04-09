<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductAddonValue;
use Illuminate\Database\Seeder;

class ProductAddonSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'burgers' => [
                ['title' => 'Burger Bun', 'selection_type' => 'single', 'values' => [['Classic Bun', null], ['Sesame Bun', 0.50], ['Brioche Bun', 0.80]]],
                ['title' => 'Extra Toppings', 'selection_type' => 'multiple', 'values' => [['Cheese', 1.00], ['Jalapenos', 0.60], ['Mushrooms', 0.90]]],
                ['title' => 'Sauce Options', 'selection_type' => 'multiple', 'values' => [['Mayo', null], ['BBQ Sauce', 0.40], ['Spicy Sauce', 0.50]]],
            ],
            'drinks' => [
                ['title' => 'Drink Size', 'selection_type' => 'single', 'values' => [['Regular', null], ['Large', 0.80]]],
                ['title' => 'Ice Level', 'selection_type' => 'single', 'values' => [['No Ice', null], ['Less Ice', null], ['Extra Ice', null]]],
                ['title' => 'Add Ons', 'selection_type' => 'multiple', 'values' => [['Lemon Slice', 0.20], ['Mint', 0.30], ['Extra Shot Syrup', 0.50]]],
            ],
            'pizza' => [
                ['title' => 'Crust Type', 'selection_type' => 'single', 'values' => [['Thin Crust', null], ['Pan Crust', 1.20], ['Stuffed Crust', 2.00]]],
                ['title' => 'Extra Toppings', 'selection_type' => 'multiple', 'values' => [['Olives', 0.80], ['Mushrooms', 1.00], ['Extra Cheese', 1.50]]],
                ['title' => 'Seasoning & Sauce', 'selection_type' => 'multiple', 'values' => [['Oregano', null], ['Chili Flakes', null], ['Garlic Dip', 0.70]]],
            ],
        ];

        // Clear existing data
        \Illuminate\Support\Facades\DB::table('product_addon_product')->truncate();
        ProductAddonValue::query()->delete();
        ProductAddon::query()->delete();

        foreach ($templates as $categoryPermalink => $addons) {
            $category = \App\Models\Category::where('permalink', $categoryPermalink)->first();
            if (!$category) continue;

            $productIds = Product::where('category_id', $category->id)->pluck('id')->toArray();
            if (empty($productIds)) continue;

            foreach ($addons as $addonData) {
                // Create the shared addon
                $addon = ProductAddon::create([
                    'title' => $addonData['title'],
                    'selection_type' => $addonData['selection_type'],
                    'status' => true,
                ]);

                // Create values for the addon
                foreach ($addonData['values'] as [$title, $price]) {
                    $addon->values()->create([
                        'title' => $title,
                        'price' => $price,
                        'status' => true
                    ]);
                }

                // Attach the addon to all products in this category
                $addon->products()->attach($productIds);
            }
        }
    }
}
