<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use Database\Seeders\Concerns\GeoSeedData;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    use GeoSeedData;

    public function run(): void
    {
        $regionMap = $this->regionMap();

        foreach (Country::orderBy('name')->get() as $country) {
            $regions = $regionMap[$country->name] ?? [
                $country->name . ' North Region',
                $country->name . ' Central Region',
                $country->name . ' South Region',
            ];

            foreach ($regions as $regionName) {
                Region::updateOrCreate(
                    [
                        'name' => $regionName,
                        'country_id' => $country->id,
                    ],
                    [
                        'status' => true,
                    ]
                );
            }
        }
    }
}
