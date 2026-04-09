<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories.manage', 'categories.view', 'categories.add', 'categories.edit', 'categories.delete'] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $moduleId = DB::table('sidebar_modules')->where('permission', 'categories')->value('id');

        if (! $moduleId) {
            $moduleId = DB::table('sidebar_modules')->insertGetId([
                'title' => 'Categories',
                'icon' => 'fa-tags',
                'permission' => 'categories',
                'order' => 3,
            ]);
        }

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'categories.manage'],
            [
                'sidebar_module_id' => $moduleId,
                'title' => 'All Categories',
                'permission' => 'categories.manage',
                'order' => 1,
            ]
        );
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'categories.manage')->delete();
        DB::table('sidebar_modules')->where('permission', 'categories')->delete();
        DB::table('permissions')->whereIn('name', ['categories.manage', 'categories.view', 'categories.add', 'categories.edit', 'categories.delete'])->delete();
    }
};
