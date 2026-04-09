<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'countries.manage',
            'countries.view',
            'countries.add',
            'countries.edit',
            'countries.delete',
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $settingsModuleId = DB::table('sidebar_modules')->where('title', 'Settings')->value('id');
        if ($settingsModuleId) {
            DB::table('sidebar_options')->updateOrInsert(
                ['route' => 'countries.manage'],
                [
                    'sidebar_module_id' => $settingsModuleId,
                    'title' => 'Countries',
                    'permission' => 'countries.manage',
                    'order' => 4,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('sidebar_options')->where('route', 'organizations.manage')->update(['order' => 5, 'updated_at' => now()]);
            DB::table('sidebar_options')->where('route', 'regions.manage')->update(['order' => 6, 'updated_at' => now()]);
            DB::table('sidebar_options')->where('route', 'locations.manage')->update(['order' => 7, 'updated_at' => now()]);
            DB::table('sidebar_options')->where('route', 'sidebar.manage')->update(['order' => 8, 'updated_at' => now()]);
            DB::table('sidebar_options')->where('route', 'email.manage')->update(['order' => 9, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'countries.manage',
            'countries.view',
            'countries.add',
            'countries.edit',
            'countries.delete',
        ])->delete();

        DB::table('sidebar_options')->where('route', 'countries.manage')->delete();
    }
};
