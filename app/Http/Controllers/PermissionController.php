<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'permissions.manage', ['permissions.view', 'permissions.add', 'permissions.edit', 'permissions.delete', 'permissions.manage'])) {
            abort(403);
        }

        $permissions = Permission::all();
        return view('permissions.manage', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name|max:255',
        ]);

        Permission::create([
            'name'       => strtolower(str_replace(' ', '-', $request->name)),
            'status'     => $request->has('status') ? 1 : 0,
            'guard_name' => 'web',
        ]);

        return redirect()->route('permissions.manage')->with('success', 'Permission created successfully!');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->back()->with('success', 'Permission Deleted');
    }
}
