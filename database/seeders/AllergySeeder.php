<?php

namespace Database\Seeders;

use App\Models\Allergy;
use Illuminate\Database\Seeder;

class AllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allergies = [
            ['code' => 'A', 'title' => 'Gluten (often subdivided: A1 for Wheat, A2 for Rye)'],
            ['code' => 'B', 'title' => 'Crustaceans (Shrimp/Lobster)'],
            ['code' => 'C', 'title' => 'Eggs'],
            ['code' => 'D', 'title' => 'Fish'],
            ['code' => 'E', 'title' => 'Peanuts'],
            ['code' => 'F', 'title' => 'Soy'],
            ['code' => 'G', 'title' => 'Milk/Lactose'],
            ['code' => 'H', 'title' => 'Nuts (e.g., H1 Almonds, H2 Hazelnuts)'],
            ['code' => 'L', 'title' => 'Celery'],
            ['code' => '1', 'title' => 'With Colorants (Farbstoff)'],
            ['code' => '2', 'title' => 'With Preservatives (Konservierungsstoffe)'],
            ['code' => '3', 'title' => 'With Antioxidants'],
            ['code' => '4', 'title' => 'With Flavor Enhancers (Geschmacksverstärker - very common in Asian food)'],
            ['code' => '5', 'title' => 'Sulphured (Geschwefelt)'],
            ['code' => '6', 'title' => 'Blackened (mostly for olives)'],
            ['code' => '7', 'title' => 'Waxed (mostly for fruit peels)'],
            ['code' => '8', 'title' => 'With Phosphate'],
            ['code' => '9', 'title' => 'With Sweeteners'],
            ['code' => '10', 'title' => 'Contains Phenylalanine'],
        ];

        foreach ($allergies as $allergy) {
            Allergy::updateOrCreate(
                ['code' => $allergy['code']],
                ['title' => $allergy['title'], 'status' => true]
            );
        }
    }
}
