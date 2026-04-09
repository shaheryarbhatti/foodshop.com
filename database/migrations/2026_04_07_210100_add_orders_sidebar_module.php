<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'orders.manage',
            'orders.view',
            'orders.edit',
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $ordersModuleId = DB::table('sidebar_modules')->where('permission', 'orders')->value('id');
        if (! $ordersModuleId) {
            $ordersModuleId = DB::table('sidebar_modules')->insertGetId([
                'title' => 'Order',
                'icon' => 'fa-shopping-bag',
                'permission' => 'orders',
                'order' => 7,
            ]);
        } else {
            DB::table('sidebar_modules')->where('id', $ordersModuleId)->update([
                'title' => 'Order',
                'icon' => 'fa-shopping-bag',
                'order' => 7,
                'updated_at' => now(),
            ]);
        }

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'orders.manage'],
            [
                'sidebar_module_id' => $ordersModuleId,
                'title' => 'All Orders',
                'permission' => 'orders.manage',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('sidebar_modules')
            ->where('permission', 'customers')
            ->update(['order' => 8, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'orders.manage')->delete();
        DB::table('sidebar_modules')->where('permission', 'orders')->delete();
        DB::table('permissions')->whereIn('name', [
            'orders.manage',
            'orders.view',
            'orders.edit',
        ])->delete();

        DB::table('sidebar_modules')
            ->where('permission', 'customers')
            ->update(['order' => 7, 'updated_at' => now()]);
    }
};
