<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]
        );

        $this->call([
            SidebarSeeder::class,
            CategorySeeder::class,
            OrganizationSeeder::class,
            CountrySeeder::class,
            RegionSeeder::class,
            LocationSeeder::class,
            ProductSeeder::class,
            ProductAddonSeeder::class,
            TaxSeeder::class,
            ShippingFeeSeeder::class,
        ]);
    }
}
