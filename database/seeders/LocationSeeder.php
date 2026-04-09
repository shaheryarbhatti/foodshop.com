<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Region;
use Database\Seeders\Concerns\GeoSeedData;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    use GeoSeedData;

    public function run(): void
    {
        $cityMap = $this->cityMap();

        foreach (Region::with('country')->orderBy('id')->get() as $region) {
            $countryName = optional($region->country)->name;
            $cities = $cityMap[$countryName] ?? [];

            if (empty($cities)) {
                $cities = [
                    $region->name . ' City',
                    $region->name . ' Central City',
                    $region->name . ' Metro City',
                ];
            } else {
                $baseOffset = ($region->id - 1) % max(count($cities), 1);
                $cities = [
                    $cities[$baseOffset % count($cities)],
                    $cities[($baseOffset + 1) % count($cities)],
                    $cities[($baseOffset + 2) % count($cities)],
                ];
            }

            foreach (array_values(array_unique($cities)) as $cityName) {
                Location::updateOrCreate(
                    ['name' => $cityName, 'region_id' => $region->id],
                    ['status' => true]
                );
            }
        }
    }
}
