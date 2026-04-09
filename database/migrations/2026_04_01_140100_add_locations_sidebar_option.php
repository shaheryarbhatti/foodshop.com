<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['locations.manage', 'locations.view', 'locations.add', 'locations.edit', 'locations.delete'] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $settingsModuleId = DB::table('sidebar_modules')->where('permission', 'settings')->value('id');

        if ($settingsModuleId) {
            DB::table('sidebar_options')->updateOrInsert(
                ['route' => 'locations.manage'],
                [
                    'sidebar_module_id' => $settingsModuleId,
                    'title' => 'Locations',
                    'permission' => 'locations.manage',
                    'order' => 8,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'locations.manage')->delete();
        DB::table('permissions')->whereIn('name', [
            'locations.manage',
            'locations.view',
            'locations.add',
            'locations.edit',
            'locations.delete',
        ])->delete();
    }
};
