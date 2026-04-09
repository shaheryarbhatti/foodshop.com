<?php
namespace App\Http\Controllers;

use App\Models\SidebarModule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'roles.manage', ['roles.view', 'roles.add', 'roles.edit', 'roles.delete', 'roles.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Role::with('permissions')->select('roles.*');
            $isSuper = $user && strtolower((string) $user->email) === 'test@example.com';
            if (! $isSuper) {
                $query->where('name', '!=', 'Super Admin');
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('role_name', function ($row) {
                    return '<span class="fw-bold text-dark">' . strtoupper($row->name) . '</span>';
                })
                ->addColumn('permissions_summary', function ($row) {
                    $count = $row->permissions->count();
                    if ($count == 0) {
                        return '<i class="text-muted">' . __('no_permissions_assigned') . '</i>';
                    }

                    $html = '<div class="d-flex flex-wrap gap-1">';
                    foreach ($row->permissions->take(5) as $perm) {
                        $html .= '<span class="badge rounded-pill bg-light text-primary border">' . $perm->name . '</span>';
                    }
                    if ($count > 5) {
                        $html .= '<small class="text-muted ms-1">+' . ($count - 5) . ' more</small>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) use ($user) {
                    $canEdit = $this->userCanAny($user, ['roles.edit']);
                    $canDelete = $this->userCanAny($user, ['roles.delete']);
                    $editBtn = $canEdit ? '<a href="' . route('roles.edit', $row->id) . '" class="btn btn-warning btn-sm me-1">
                                <i class="fa fa-pencil"></i>
                            </a>' : '';

                    $deleteBtn = '';
                    // Safety check: Never allow deleting the core administrator role
                    if ($row->name !== 'Super Admin' && $canDelete) {
                        $deleteBtn = '<form action="' . route('roles.destroy', $row->id) . '" method="POST" style="display:inline;" class="delete-form js-confirm-delete" data-btn-gap="true">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>';
                    }

                    if (! $editBtn && ! $deleteBtn) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="action d-flex">' . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['role_name', 'permissions_summary', 'action'])
                ->make(true);
        }

        $modules = \App\Models\SidebarModule::with('options')->get();
        return view('setting.roles.manage', compact('modules'));
    }
    public function create()
    {
        // Fetch all modules with their options to display in the permission grid
        $modules = SidebarModule::with('options')->orderBy('id', 'asc')->get();

        return view('setting.roles.add', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        // 1. Create the Role
        $role = Role::create(['name' => $request->name]);

        // 2. Assign selected permissions (auto-include module-level permissions when any sub-option is selected)
        if ($request->has('permissions')) {
            $selectedPermissions = collect($request->permissions);

            // If any module sub-option is selected, ensure the module permission is also assigned.
            $modules = SidebarModule::with('options')->get();
            foreach ($modules as $module) {
                if (! $module->permission) {
                    continue;
                }

                $optionPermissions = $module->options->pluck('permission')->filter();
                if ($selectedPermissions->intersect($optionPermissions)->isNotEmpty()) {
                    $selectedPermissions->push($module->permission);
                }
            }

            $selectedPermissions = $selectedPermissions->unique()->filter();

            foreach ($selectedPermissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }

            $role->syncPermissions($selectedPermissions->toArray());
        }

        return redirect()->route('roles.manage')->with('success', 'Role created with permissions!');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        // Get all modules and options for the grid
        $modules = SidebarModule::with('options')->orderBy('id', 'asc')->get();

        // Get the names of all permissions currently assigned to this role
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('setting.roles.edit', compact('role', 'modules', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'        => 'required|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);

        // 1. Update Role Name
        $role->update(['name' => $request->name]);

        // 2. Sync Permissions (auto-include module permission when any sub-option is selected)
        $selectedPermissions = collect($request->input('permissions', []));

        if ($selectedPermissions->isNotEmpty()) {
            $modules = SidebarModule::with('options')->get();
            foreach ($modules as $module) {
                if (! $module->permission) {
                    continue;
                }

                $optionPermissions = $module->options->pluck('permission')->filter();
                if ($selectedPermissions->intersect($optionPermissions)->isNotEmpty()) {
                    $selectedPermissions->push($module->permission);
                }
            }

            $selectedPermissions = $selectedPermissions->unique()->filter();

            foreach ($selectedPermissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }

            $role->syncPermissions($selectedPermissions->toArray());
        } else {
            // If no checkboxes are selected, remove all permissions
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.manage')->with('success', __('role_updated_successfully'));
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            return redirect()->back()->with('error', 'Cannot delete protected role.');
        }
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully.');
    }
}
