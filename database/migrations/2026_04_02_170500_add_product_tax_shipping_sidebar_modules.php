<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'products.manage', 'products.view', 'products.add', 'products.edit', 'products.delete',
            'addons.manage', 'addons.view', 'addons.add', 'addons.edit', 'addons.delete',
            'taxes.manage', 'taxes.view', 'taxes.add', 'taxes.edit', 'taxes.delete',
            'shipping.manage', 'shipping.view', 'shipping.add', 'shipping.edit', 'shipping.delete',
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $productsModuleId = DB::table('sidebar_modules')->where('permission', 'products')->value('id');
        if (! $productsModuleId) {
            $productsModuleId = DB::table('sidebar_modules')->insertGetId([
                'title' => 'Products',
                'icon' => 'fa-box-open',
                'permission' => 'products',
                'order' => 4,
            ]);
        }

        $taxesModuleId = DB::table('sidebar_modules')->where('permission', 'taxes')->value('id');
        if (! $taxesModuleId) {
            $taxesModuleId = DB::table('sidebar_modules')->insertGetId([
                'title' => 'Taxes',
                'icon' => 'fa-percent',
                'permission' => 'taxes',
                'order' => 5,
            ]);
        }

        $shippingModuleId = DB::table('sidebar_modules')->where('permission', 'shipping')->value('id');
        if (! $shippingModuleId) {
            $shippingModuleId = DB::table('sidebar_modules')->insertGetId([
                'title' => 'Shipping Fees',
                'icon' => 'fa-truck',
                'permission' => 'shipping',
                'order' => 6,
            ]);
        }

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'products.manage'],
            ['sidebar_module_id' => $productsModuleId, 'title' => 'All Products', 'permission' => 'products.manage', 'order' => 1]
        );

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'addons.manage'],
            ['sidebar_module_id' => $productsModuleId, 'title' => 'Addons', 'permission' => 'addons.manage', 'order' => 2]
        );

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'taxes.manage'],
            ['sidebar_module_id' => $taxesModuleId, 'title' => 'All Taxes', 'permission' => 'taxes.manage', 'order' => 1]
        );

        DB::table('sidebar_options')->updateOrInsert(
            ['route' => 'shipping.manage'],
            ['sidebar_module_id' => $shippingModuleId, 'title' => 'All Shipping Fees', 'permission' => 'shipping.manage', 'order' => 1]
        );
    }

    public function down(): void
    {
        DB::table('sidebar_options')->whereIn('route', ['products.manage', 'addons.manage', 'taxes.manage', 'shipping.manage'])->delete();
        DB::table('sidebar_modules')->whereIn('permission', ['products', 'taxes', 'shipping'])->delete();
        DB::table('permissions')->whereIn('name', [
            'products.manage', 'products.view', 'products.add', 'products.edit', 'products.delete',
            'addons.manage', 'addons.view', 'addons.add', 'addons.edit', 'addons.delete',
            'taxes.manage', 'taxes.view', 'taxes.add', 'taxes.edit', 'taxes.delete',
            'shipping.manage', 'shipping.view', 'shipping.add', 'shipping.edit', 'shipping.delete',
        ])->delete();
    }
};
