<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'customers.manage', 'customers.view', 'customers.add', 'customers.edit', 'customers.delete',
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $moduleId = DB::table('sidebar_modules')->where('permission', 'customers')->value('id');
        if (! $moduleId) {
            $moduleId = DB::table('sidebar_modules')->insertGetId([
                'title' => 'Customers',
                'icon' => 'fa-users',
                'permission' => 'customers',
                'order' => 7,
            ]);
        }

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'customers.manage'],
            ['sidebar_module_id' => $moduleId, 'title' => 'All Customers', 'permission' => 'customers.manage', 'order' => 1]
        );
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'customers.manage')->delete();
        DB::table('sidebar_modules')->where('permission', 'customers')->delete();
        DB::table('permissions')->whereIn('name', [
            'customers.manage', 'customers.view', 'customers.add', 'customers.edit', 'customers.delete',
        ])->delete();
    }
};
