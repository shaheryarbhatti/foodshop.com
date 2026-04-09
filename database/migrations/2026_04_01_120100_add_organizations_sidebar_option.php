<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['organizations.manage', 'organizations.view', 'organizations.add', 'organizations.edit', 'organizations.delete'] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $settingsModuleId = DB::table('sidebar_modules')->where('permission', 'settings')->value('id');

        if ($settingsModuleId) {
            DB::table('sidebar_options')->updateOrInsert(
                ['route' => 'organizations.manage'],
                [
                    'sidebar_module_id' => $settingsModuleId,
                    'title' => 'Branches',
                    'permission' => 'organizations.manage',
                    'order' => 6,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'organizations.manage')->delete();
        DB::table('permissions')->whereIn('name', [
            'organizations.manage',
            'organizations.view',
            'organizations.add',
            'organizations.edit',
            'organizations.delete',
        ])->delete();
    }
};
