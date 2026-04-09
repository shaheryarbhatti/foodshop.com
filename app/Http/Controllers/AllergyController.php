<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use Illuminate\Http\Request;

class AllergyController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'products.manage', ['products.view', 'products.add', 'products.edit', 'products.delete', 'products.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Allergy::query();

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('bulk_select', fn ($allergy) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $allergy->id . '">')
                ->addColumn('status_badge', function ($allergy) {
                    return $allergy->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($allergy) use ($user) {
                    $canEdit = $this->userCanAny($user, ['products.edit']);
                    $canDelete = $this->userCanAny($user, ['products.delete']);

                    $edit = $canEdit ? '<a href="' . route('allergies.edit', $allergy->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('allergies.destroy', $allergy->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';

                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['bulk_select', 'status_badge', 'action'])
                ->make(true);
        }

        return view('allergies.manage');
    }

    public function create()
    {
        $this->authorizeAction('products.add');
        return view('allergies.add');
    }

    public function store(Request $request)
    {
        $this->authorizeAction('products.add');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:allergies,code',
            'status' => 'required|boolean',
        ]);

        Allergy::create($validated);

        return redirect()->route('allergies.manage')->with('success', __('allergy_created_successfully'));
    }

    public function edit(Allergy $allergy)
    {
        $this->authorizeAction('products.edit');
        return view('allergies.edit', compact('allergy'));
    }

    public function update(Request $request, Allergy $allergy)
    {
        $this->authorizeAction('products.edit');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:allergies,code,' . $allergy->id,
            'status' => 'required|boolean',
        ]);

        $allergy->update($validated);

        return redirect()->route('allergies.manage')->with('success', __('allergy_updated_successfully'));
    }

    public function destroy(Allergy $allergy)
    {
        $this->authorizeAction('products.delete');
        $allergy->delete();
        return redirect()->route('allergies.manage')->with('success', __('allergy_deleted_successfully'));
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorizeAction('products.delete');
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            Allergy::whereIn('id', $ids)->delete();
        }
        return response()->json(['success' => true]);
    }

    private function authorizeAction(string $permission)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }
}
