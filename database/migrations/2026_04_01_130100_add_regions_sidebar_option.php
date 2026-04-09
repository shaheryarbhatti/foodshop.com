<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['regions.manage', 'regions.view', 'regions.add', 'regions.edit', 'regions.delete'] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $settingsModuleId = DB::table('sidebar_modules')->where('permission', 'settings')->value('id');

        if ($settingsModuleId) {
            DB::table('sidebar_options')->updateOrInsert(
                ['route' => 'regions.manage'],
                [
                    'sidebar_module_id' => $settingsModuleId,
                    'title' => 'Regions',
                    'permission' => 'regions.manage',
                    'order' => 7,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'regions.manage')->delete();
        DB::table('permissions')->whereIn('name', [
            'regions.manage',
            'regions.view',
            'regions.add',
            'regions.edit',
            'regions.delete',
        ])->delete();
    }
};
