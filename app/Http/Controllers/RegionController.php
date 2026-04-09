<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'regions.manage', ['regions.view', 'regions.add', 'regions.edit', 'regions.delete', 'regions.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Region::with('country')->select('regions.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('country_name', function ($region) {
                    return e(optional($region->country)->name ?: '-');
                })
                ->addColumn('status_badge', function ($region) {
                    return $region->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($region) use ($user) {
                    $canEdit = $this->userCanAny($user, ['regions.edit']);
                    $canDelete = $this->userCanAny($user, ['regions.delete']);

                    $edit = $canEdit
                        ? '<a href="' . route('regions.edit', $region->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>'
                        : '';
                    $delete = $canDelete
                        ? '<form action="' . route('regions.destroy', $region->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">'
                            . csrf_field() . method_field('DELETE') .
                            '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>'
                        : '';

                    if (! $edit && ! $delete) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="action d-flex gap-2">' . $edit . $delete . '</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('regions.manage');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['regions.add'])) {
            abort(403);
        }

        $countries = Country::where('status', true)->orderBy('name')->get();
        return view('regions.add', compact('countries'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['regions.add'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'status' => 'required|boolean',
        ]);

        Region::create($validated);

        return redirect()->route('regions.manage')
            ->with('success', __('region_created_successfully'));
    }

    public function edit(Region $region)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['regions.edit'])) {
            abort(403);
        }

        $countries = Country::where('status', true)->orderBy('name')->get();
        return view('regions.edit', compact('region', 'countries'));
    }

    public function update(Request $request, Region $region)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['regions.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'status' => 'required|boolean',
        ]);

        $region->update($validated);

        return redirect()->route('regions.manage')
            ->with('success', __('region_updated_successfully'));
    }

    public function destroy(Region $region)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['regions.delete'])) {
            abort(403);
        }

        $region->delete();

        return redirect()->route('regions.manage')
            ->with('success', __('region_deleted_successfully'));
    }
}
