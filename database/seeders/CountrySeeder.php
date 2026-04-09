<?php

namespace Database\Seeders;

use App\Models\Country;
use Database\Seeders\Concerns\GeoSeedData;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    use GeoSeedData;

    public function run(): void
    {
        $countryCodeMap = $this->countryCodeMap();

        foreach ($this->countryNames() as $countryName) {
            Country::updateOrCreate(
                ['name' => $countryName],
                [
                    'country_code' => $countryCodeMap[$countryName] ?? null,
                    'status' => true,
                ]
            );
        }
    }
}
