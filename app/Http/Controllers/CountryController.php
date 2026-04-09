<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'countries.manage', ['countries.view', 'countries.add', 'countries.edit', 'countries.delete', 'countries.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Country::query()->select('countries.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->editColumn('country_code', function ($country) {
                    return $country->country_code ?: '-';
                })
                ->addColumn('status_badge', function ($country) {
                    return $country->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($country) use ($user) {
                    $canEdit = $this->userCanAny($user, ['countries.edit']);
                    $canDelete = $this->userCanAny($user, ['countries.delete']);

                    $edit = $canEdit
                        ? '<a href="' . route('countries.edit', $country->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>'
                        : '';
                    $delete = $canDelete
                        ? '<form action="' . route('countries.destroy', $country->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">'
                            . csrf_field() . method_field('DELETE') .
                            '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>'
                        : '';

                    return $edit || $delete
                        ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>'
                        : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('countries.manage');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['countries.add'])) {
            abort(403);
        }

        return view('countries.add');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['countries.add'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'country_code' => 'required|string|size:2|unique:countries,country_code',
            'status' => 'required|boolean',
        ]);

        $validated['country_code'] = strtoupper($validated['country_code']);

        Country::create($validated);

        return redirect()->route('countries.manage')->with('success', __('country_created_successfully'));
    }

    public function edit(Country $country)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['countries.edit'])) {
            abort(403);
        }

        return view('countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['countries.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
            'country_code' => 'required|string|size:2|unique:countries,country_code,' . $country->id,
            'status' => 'required|boolean',
        ]);

        $validated['country_code'] = strtoupper($validated['country_code']);

        $country->update($validated);

        return redirect()->route('countries.manage')->with('success', __('country_updated_successfully'));
    }

    public function destroy(Country $country)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['countries.delete'])) {
            abort(403);
        }

        $country->delete();

        return redirect()->route('countries.manage')->with('success', __('country_deleted_successfully'));
    }
}
