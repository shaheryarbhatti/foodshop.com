<?php
namespace App\Http\Controllers;

use App\Models\SidebarModule;
use App\Models\SidebarOption;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class SidebarManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'sidebar.manage', ['sidebar.view', 'sidebar.add', 'sidebar.edit', 'sidebar.delete', 'sidebar.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = SidebarModule::with('options')->select('sidebar_modules.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('module_info', function ($row) {
                    return '<div>
                            <strong>' . __($row->title) . '</strong><br>
                            <small class="text-muted">
                                <i class="fa ' . $row->icon . ' me-1"></i>
                                ' . __('permission') . ': ' . $row->permission . '
                            </small>
                        </div>';
                })
                ->addColumn('options_list', function ($row) use ($user) {
                    $canDelete = $this->userCanAny($user, ['sidebar.delete']);
                    if ($row->options->isEmpty()) {
                        return '<i class="text-muted">' . __('no_options_added') . '</i>';
                    }

                    $html = '<ul class="list-unstyled mb-0">';
                    foreach ($row->options as $option) {
                        // Specific Delete Form for each option
                        $optionDeleteUrl = route('sidebar.option.destroy', $option->id);
                        $html .= '<li class="mb-2 d-flex align-items-center justify-content-between border-bottom pb-1">
                                <div>
                                    <span class="badge rounded-pill bg-light text-primary border">' . __($option->title) . '</span>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">' . $option->route . '</small>
                                </div>
                                <div class="d-flex gap-2">';
                        if ($canDelete) {
                            $html .= '<form action="' . $optionDeleteUrl . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">
                                            ' . csrf_field() . method_field('DELETE') . '
                                            <button type="submit" class="border-0 bg-transparent text-danger p-0"><i class="fa fa-times-circle"></i></button>
                                        </form>';
                        }
                        $html .= '</div>
                              </li>';
                    }
                    $html .= '</ul>';
                    return $html;
                })
                ->addColumn('action', function ($row) use ($user) {
                    $canDelete = $this->userCanAny($user, ['sidebar.delete']);
                    // Main Module Actions (Edit & Delete entire module)
                   
                    $delete = $canDelete ? '<form action="' . route('sidebar.module.destroy', $row->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>' : '';

                    if (! $delete) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="action d-flex gap-2">' . $delete . '</div>';
                })
                ->rawColumns(['module_info', 'options_list', 'action'])
                ->make(true);
        }

        $modules = SidebarModule::all();
        return view('setting.sidebar.manage', compact('modules'));
    }

    public function create()
    {
        $modules = SidebarModule::with('options')->get();
        return view('setting.sidebar.add', compact('modules'));
    }

    public function storeModule(Request $request)
    {
        $request->validate([
            'title'      => 'required',
            'icon'       => 'required',
            'permission' => 'required|unique:permissions,name',
        ]);

        // 1. Create the Spatie Permission first
        Permission::firstOrCreate(['name' => $request->permission, 'guard_name' => 'web']);

        // 2. Save the Module
        SidebarModule::create($request->all());

        return back()->with('success', 'Module and Permission created!');
    }

    public function storeOption(Request $request)
    {
        $request->validate([
            'sidebar_module_id' => 'required',
            'title'             => 'required',
            'route'             => 'required',
            'permission'        => 'required',
        ]);

        // 1. Create the Spatie Permission
        Permission::firstOrCreate(['name' => $request->permission, 'guard_name' => 'web']);

        // 2. Save the Option
        SidebarOption::create($request->all());

        return back()->with('success', 'Option added to module!');
    }

    public function destroyModule(SidebarModule $module)
    {
        // onDelete('cascade') in migration will handle sidebar_options
        $module->delete();
        return back()->with('success', __('Module and its options deleted!'));
    }

    public function destroyOption($id)
    {
        $option = SidebarOption::findOrFail($id);
        $option->delete();

        return redirect()->back()->with('option_success', __('option_deleted_successfully'));
    }
}
