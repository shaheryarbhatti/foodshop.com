<?php
namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = app('router');
        $groups = method_exists($router, 'getMiddlewareGroups') ? $router->getMiddlewareGroups() : [];
        $web = $groups['web'] ?? [];
        if (! in_array(\App\Http\Middleware\AccessGate::class, $web, true)) {
            abort(403, 'License enforcement is required.');
        }

        try {
            if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
                $superAdmin = Role::where('name', 'Super Admin')->first();
                if ($superAdmin) {
                    $allPermissions = Permission::all();
                    if ($superAdmin->permissions()->count() !== $allPermissions->count()) {
                        $superAdmin->syncPermissions($allPermissions);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Avoid blocking boot if permissions tables are unavailable.
        }
    }
}
