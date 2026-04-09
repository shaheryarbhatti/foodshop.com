<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sidebar_options')->where('permission', 'permissions.manage')->delete();
    }

    public function down(): void
    {
        $settingsModuleId = DB::table('sidebar_modules')->where('permission', 'settings')->value('id');

        if (! $settingsModuleId) {
            return;
        }

        DB::table('sidebar_options')->updateOrInsert(
            ['permission' => 'permissions.manage'],
            [
                'sidebar_module_id' => $settingsModuleId,
                'title' => 'Permissions',
                'route' => 'permissions.manage',
                'order' => 1,
            ]
        );
    }
};
