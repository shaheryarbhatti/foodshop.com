<?php

namespace Database\Seeders;

use App\Models\SidebarModule;
use App\Models\SidebarOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SidebarSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        SidebarOption::query()->delete();
        SidebarModule::query()->delete();

        SidebarModule::create(['title' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'permission' => 'dashboard', 'order' => 1]);
        $settingsModule = SidebarModule::create(['title' => 'Settings', 'icon' => 'fa-cogs', 'permission' => 'settings', 'order' => 2]);
        $categoriesModule = SidebarModule::create(['title' => 'Categories', 'icon' => 'fa-tags', 'permission' => 'categories', 'order' => 3]);
        $productsModule = SidebarModule::create(['title' => 'Products', 'icon' => 'fa-box-open', 'permission' => 'products', 'order' => 4]);
        $taxesModule = SidebarModule::create(['title' => 'Taxes', 'icon' => 'fa-percent', 'permission' => 'taxes', 'order' => 5]);
        $shippingModule = SidebarModule::create(['title' => 'Shipping Fees', 'icon' => 'fa-truck', 'permission' => 'shipping', 'order' => 6]);
        $ordersModule = SidebarModule::create(['title' => 'Order', 'icon' => 'fa-shopping-bag', 'permission' => 'orders', 'order' => 7]);
        $customersModule = SidebarModule::create(['title' => 'Customers', 'icon' => 'fa-users', 'permission' => 'customers', 'order' => 8]);

        SidebarOption::create(['sidebar_module_id' => $categoriesModule->id, 'title' => 'All Categories', 'route' => 'categories.manage', 'permission' => 'categories.manage', 'order' => 1]);
        SidebarOption::create(['sidebar_module_id' => $productsModule->id, 'title' => 'All Products', 'route' => 'products.manage', 'permission' => 'products.manage', 'order' => 1]);
        SidebarOption::create(['sidebar_module_id' => $productsModule->id, 'title' => 'Addons', 'route' => 'addons.manage', 'permission' => 'addons.manage', 'order' => 2]);
        SidebarOption::create(['sidebar_module_id' => $taxesModule->id, 'title' => 'All Taxes', 'route' => 'taxes.manage', 'permission' => 'taxes.manage', 'order' => 1]);
        SidebarOption::create(['sidebar_module_id' => $shippingModule->id, 'title' => 'All Shipping Fees', 'route' => 'shipping.manage', 'permission' => 'shipping.manage', 'order' => 1]);
        SidebarOption::create(['sidebar_module_id' => $ordersModule->id, 'title' => 'All Orders', 'route' => 'orders.manage', 'permission' => 'orders.manage', 'order' => 1]);
        SidebarOption::create(['sidebar_module_id' => $customersModule->id, 'title' => 'All Customers', 'route' => 'customers.manage', 'permission' => 'customers.manage', 'order' => 1]);

        foreach ([
            ['Roles', 'roles.manage', 'roles.manage', 1],
            ['Users', 'users.manage', 'users.manage', 2],
            ['Currencies', 'currencies.manage', 'currencies.manage', 3],
            ['Countries', 'countries.manage', 'countries.manage', 4],
            ['Branches', 'organizations.manage', 'organizations.manage', 5],
            ['Regions', 'regions.manage', 'regions.manage', 6],
            ['Locations', 'locations.manage', 'locations.manage', 7],
            ['Sidebar Management', 'sidebar.manage', 'sidebar.manage', 8],
            ['Email Template', 'email.manage', 'email.manage', 9]
        ] as [$title, $route, $permission, $order]) {
            SidebarOption::create(['sidebar_module_id' => $settingsModule->id, 'title' => $title, 'route' => $route, 'permission' => $permission, 'order' => $order]);
        }
    }
}
