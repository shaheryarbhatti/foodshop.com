<?php

namespace App\Http\Controllers;

use App\Models\SidebarOption;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function canAccessRoute(?User $user, string $routeName, array $fallbackPermissions = []): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $permissions = collect($fallbackPermissions);
        $option = SidebarOption::with('module')->where('route', $routeName)->first();
        if ($option) {
            if (! empty($option->permission)) {
                $permissions->push($option->permission);
            }
            if ($option->module && ! empty($option->module->permission)) {
                $permissions->push($option->module->permission);
            }
        }

        $routeBase = explode('.', $routeName)[0] ?? $routeName;
        $legacyBase = 'manage-' . Str::singular($routeBase);
        $actionPermissions = [
            $routeBase . '.view',
            $routeBase . '.add',
            $routeBase . '.edit',
            $routeBase . '.delete',
            $legacyBase . '.view',
            $legacyBase . '.add',
            $legacyBase . '.edit',
            $legacyBase . '.delete',
        ];

        $permissions = $permissions->merge($actionPermissions)->filter()->unique()->values()->all();
        if (empty($permissions)) {
            return false;
        }

        return $this->userCanAny($user, $permissions);
    }

    protected function userCanAny(?User $user, array $permissions): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $expanded = $this->expandPermissions($permissions);
        return $user->canAny($expanded);
    }

    protected function expandPermissions(array $permissions): array
    {
        $expanded = [];
        foreach ($permissions as $permission) {
            if (! $permission) {
                continue;
            }
            $expanded[] = $permission;
            if (str_contains($permission, '.')) {
                [$base, $action] = explode('.', $permission, 2);
                $legacyBase = 'manage-' . Str::singular($base);
                $expanded[] = $legacyBase . '.' . $action;
            }
        }

        return array_values(array_unique($expanded));
    }
}
