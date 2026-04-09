<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingsModuleId = DB::table('sidebar_modules')->where('permission', 'settings')->value('id');

        if (! $settingsModuleId) {
            return;
        }

        DB::table('sidebar_options')->updateOrInsert(
            ['permission' => 'email.manage'],
            [
                'sidebar_module_id' => $settingsModuleId,
                'title' => 'Email Template',
                'route' => 'email.manage',
                'order' => 11,
            ]
        );
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('permission', 'email.manage')->delete();
    }
};
