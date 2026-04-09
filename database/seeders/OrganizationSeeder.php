<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'UniSolve Corporate', 'status' => true],
            ['name' => 'UniSolve Campus', 'status' => true],
        ] as $organization) {
            Organization::updateOrCreate(
                ['name' => $organization['name']],
                $organization
            );
        }
    }
}
