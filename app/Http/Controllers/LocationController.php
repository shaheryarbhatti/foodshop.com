<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Region;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'locations.manage', ['locations.view', 'locations.add', 'locations.edit', 'locations.delete', 'locations.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Location::with('region.country')->select('locations.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('region_name', fn ($location) => e(optional($location->region)->name ?: '-'))
                ->addColumn('country_name', fn ($location) => e(optional(optional($location->region)->country)->name ?: '-'))
                ->addColumn('status_badge', function ($location) {
                    return $location->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($location) use ($user) {
                    $canEdit = $this->userCanAny($user, ['locations.edit']);
                    $canDelete = $this->userCanAny($user, ['locations.delete']);
                    $edit = $canEdit ? '<a href="' . route('locations.edit', $location->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('locations.destroy', $location->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';

                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('locations.manage');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['locations.add'])) {
            abort(403);
        }

        return view('locations.add', ['regions' => Region::with('country')->where('status', true)->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['locations.add'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'status' => 'required|boolean',
        ]);

        Location::create($validated);

        return redirect()->route('locations.manage')->with('success', __('location_created_successfully'));
    }

    public function edit(Location $location)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['locations.edit'])) {
            abort(403);
        }

        return view('locations.edit', ['location' => $location, 'regions' => Region::with('country')->where('status', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, Location $location)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['locations.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'status' => 'required|boolean',
        ]);

        $location->update($validated);

        return redirect()->route('locations.manage')->with('success', __('location_updated_successfully'));
    }

    public function destroy(Location $location)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['locations.delete'])) {
            abort(403);
        }

        $location->delete();

        return redirect()->route('locations.manage')->with('success', __('location_deleted_successfully'));
    }
}
