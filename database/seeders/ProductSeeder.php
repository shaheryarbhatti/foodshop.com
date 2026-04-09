<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id', 'permalink');
        if ($categoryIds->isEmpty()) {
            return;
        }

        $products = [
            [
                'serial_number' => 1,
                'category_permalink' => 'burgers',
                'title' => 'Classic Beef Burger',
                'permalink' => 'classic-beef-burger',
                'description' => 'juicy beef patty with lettuce, tomato, onion and house sauce',
                'base_price' => 8.90,
            ],
            [
                'serial_number' => 2,
                'category_permalink' => 'burgers',
                'title' => 'Cheese Melt Burger',
                'permalink' => 'cheese-melt-burger',
                'description' => 'beef burger loaded with cheddar cheese and creamy mayo',
                'base_price' => 9.50,
            ],
            [
                'serial_number' => 3,
                'category_permalink' => 'burgers',
                'title' => 'Double Patty Burger',
                'permalink' => 'double-patty-burger',
                'description' => 'double beef patties with pickles, onions and burger dressing',
                'base_price' => 11.25,
            ],
            [
                'serial_number' => 4,
                'category_permalink' => 'burgers',
                'title' => 'BBQ Smoke Burger',
                'permalink' => 'bbq-smoke-burger',
                'description' => 'chargrilled beef patty with cheddar, onions and bbq glaze',
                'base_price' => 11.00,
            ],
            [
                'serial_number' => 5,
                'category_permalink' => 'burgers',
                'title' => 'Zinger Crunch Burger',
                'permalink' => 'zinger-crunch-burger',
                'description' => 'crispy chicken fillet with iceberg lettuce and spicy mayo',
                'base_price' => 10.40,
            ],
            [
                'serial_number' => 6,
                'category_permalink' => 'burgers',
                'title' => 'Chicken Grill Burger',
                'permalink' => 'chicken-grill-burger',
                'description' => 'grilled chicken breast burger with garlic mayo and fresh greens',
                'base_price' => 9.80,
            ],
            [
                'serial_number' => 7,
                'category_permalink' => 'burgers',
                'title' => 'Mushroom Swiss Burger',
                'permalink' => 'mushroom-swiss-burger',
                'description' => 'beef patty topped with sauteed mushrooms and swiss cheese',
                'base_price' => 10.90,
            ],
            [
                'serial_number' => 8,
                'category_permalink' => 'burgers',
                'title' => 'Jalapeno Fire Burger',
                'permalink' => 'jalapeno-fire-burger',
                'description' => 'spicy beef burger with jalapenos, pepper jack and chipotle sauce',
                'base_price' => 10.60,
            ],
            [
                'serial_number' => 9,
                'category_permalink' => 'burgers',
                'title' => 'Tower Chicken Burger',
                'permalink' => 'tower-chicken-burger',
                'description' => 'stacked chicken burger with cheese, hash brown and mayo',
                'base_price' => 11.10,
            ],
            [
                'serial_number' => 10,
                'category_permalink' => 'burgers',
                'title' => 'Mega Beef Burger',
                'permalink' => 'mega-beef-burger',
                'description' => 'large beef burger with double cheese, lettuce and burger sauce',
                'base_price' => 12.40,
            ],
            [
                'serial_number' => 11,
                'category_permalink' => 'drinks',
                'title' => 'Coca Cola Can',
                'permalink' => 'coca-cola-can',
                'description' => 'chilled coca cola served cold',
                'base_price' => 1.90,
            ],
            [
                'serial_number' => 12,
                'category_permalink' => 'drinks',
                'title' => 'Pepsi Can',
                'permalink' => 'pepsi-can',
                'description' => 'refreshing pepsi can served ice cold',
                'base_price' => 1.90,
            ],
            [
                'serial_number' => 13,
                'category_permalink' => 'drinks',
                'title' => '7up Can',
                'permalink' => '7up-can',
                'description' => 'light lemon-lime soft drink with crisp flavor',
                'base_price' => 1.85,
            ],
            [
                'serial_number' => 14,
                'category_permalink' => 'drinks',
                'title' => 'Fresh Orange Juice',
                'permalink' => 'fresh-orange-juice',
                'description' => 'freshly squeezed orange juice with natural sweetness',
                'base_price' => 3.20,
            ],
            [
                'serial_number' => 15,
                'category_permalink' => 'drinks',
                'title' => 'Mint Lemonade',
                'permalink' => 'mint-lemonade',
                'description' => 'cool lemonade blended with mint and crushed ice',
                'base_price' => 3.50,
            ],
            [
                'serial_number' => 16,
                'category_permalink' => 'drinks',
                'title' => 'Mineral Water',
                'permalink' => 'mineral-water',
                'description' => 'sealed bottle of mineral water',
                'base_price' => 1.20,
            ],
            [
                'serial_number' => 17,
                'category_permalink' => 'pizza',
                'title' => 'Margherita Pizza',
                'permalink' => 'margherita-pizza',
                'description' => 'classic tomato sauce, mozzarella and fresh basil',
                'base_price' => 12.75,
            ],
            [
                'serial_number' => 18,
                'category_permalink' => 'pizza',
                'title' => 'Pepperoni Feast Pizza',
                'permalink' => 'pepperoni-feast-pizza',
                'description' => 'loaded pepperoni pizza with mozzarella and oregano',
                'base_price' => 14.20,
            ],
            [
                'serial_number' => 19,
                'category_permalink' => 'pizza',
                'title' => 'Chicken Tikka Pizza',
                'permalink' => 'chicken-tikka-pizza',
                'description' => 'spicy chicken tikka with onions, capsicum and cheese',
                'base_price' => 13.90,
            ],
            [
                'serial_number' => 20,
                'category_permalink' => 'pizza',
                'title' => 'Veggie Supreme Pizza',
                'permalink' => 'veggie-supreme-pizza',
                'description' => 'loaded with olives, mushrooms, onions and bell peppers',
                'base_price' => 12.95,
            ],
            [
                'serial_number' => 21,
                'category_permalink' => 'pizza',
                'title' => 'BBQ Chicken Pizza',
                'permalink' => 'bbq-chicken-pizza',
                'description' => 'bbq chicken, mozzarella, onions and smoky sauce',
                'base_price' => 14.50,
            ],
            [
                'serial_number' => 22,
                'category_permalink' => 'pizza',
                'title' => 'Cheese Lovers Pizza',
                'permalink' => 'cheese-lovers-pizza',
                'description' => 'mozzarella-rich pizza with extra cheese topping',
                'base_price' => 13.40,
            ],
            [
                'serial_number' => 23,
                'category_permalink' => 'pizza',
                'title' => 'Fajita Pizza',
                'permalink' => 'fajita-pizza',
                'description' => 'fajita chicken, onions and capsicum over spicy sauce',
                'base_price' => 13.80,
            ],
            [
                'serial_number' => 24,
                'category_permalink' => 'pizza',
                'title' => 'Hot and Spicy Pizza',
                'permalink' => 'hot-and-spicy-pizza',
                'description' => 'spicy chicken, jalapenos and chili flakes with cheese',
                'base_price' => 14.10,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['permalink' => $product['permalink']],
                [
                    'serial_number' => $product['serial_number'],
                    'category_id' => $categoryIds[$product['category_permalink']],
                    'title' => $product['title'],
                    'permalink' => $product['permalink'],
                    'description' => $product['description'],
                    'base_price' => $product['base_price'],
                    'status' => true,
                ]
            );
        }
    }
}
