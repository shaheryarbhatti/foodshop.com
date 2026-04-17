<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'coupons.manage', 'coupons.view', 'coupons.add', 'coupons.edit', 'coupons.delete',
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $productsModuleId = DB::table('sidebar_modules')->where('permission', 'products')->value('id');
        if ($productsModuleId) {
            DB::table('sidebar_options')->updateOrInsert(
                ['route' => 'coupons.manage'],
                [
                    'sidebar_module_id' => $productsModuleId,
                    'title' => 'Coupon Code',
                    'permission' => 'coupons.manage',
                    'order' => 3,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('sidebar_options')->where('route', 'coupons.manage')->delete();
        DB::table('permissions')->whereIn('name', [
            'coupons.manage', 'coupons.view', 'coupons.add', 'coupons.edit', 'coupons.delete',
        ])->delete();
    }
};
