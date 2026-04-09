<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'organizations.manage', ['organizations.view', 'organizations.add', 'organizations.edit', 'organizations.delete', 'organizations.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Organization::with('location.region.country')->select('organizations.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('location_name', function ($organization) {
                    return e(optional($organization->location)->name ?: '-');
                })
                ->addColumn('latitude_value', function ($organization) {
                    return $organization->latitude !== null ? e((string) $organization->latitude) : '-';
                })
                ->addColumn('longitude_value', function ($organization) {
                    return $organization->longitude !== null ? e((string) $organization->longitude) : '-';
                })
                ->addColumn('status_badge', function ($organization) {
                    return $organization->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('map_button', function ($organization) {
                    if ($organization->latitude === null || $organization->longitude === null) {
                        return '<button type="button" class="btn btn-info btn-sm" disabled><i class="fa fa-map-marker-alt"></i></button>';
                    }

                    return '<button type="button" class="btn btn-info btn-sm js-show-branch-map" '
                        . 'data-name="' . e($organization->name) . '" '
                        . 'data-location="' . e(optional($organization->location)->name ?: '-') . '" '
                        . 'data-address="' . e($organization->address ?: '-') . '" '
                        . 'data-latitude="' . e((string) $organization->latitude) . '" '
                        . 'data-longitude="' . e((string) $organization->longitude) . '">'
                        . '<i class="fa fa-map-marker-alt"></i></button>';
                })
                ->addColumn('action', function ($organization) use ($user) {
                    $canEdit = $this->userCanAny($user, ['organizations.edit']);
                    $canDelete = $this->userCanAny($user, ['organizations.delete']);

                    $edit = $canEdit
                        ? '<a href="' . route('organizations.edit', $organization->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>'
                        : '';
                    $delete = $canDelete
                        ? '<form action="' . route('organizations.destroy', $organization->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">'
                            . csrf_field() . method_field('DELETE') .
                            '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>'
                        : '';

                    if (! $edit && ! $delete) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="action d-flex gap-2">' . $edit . $delete . '</div>';
                })
                ->rawColumns(['status_badge', 'map_button', 'action'])
                ->make(true);
        }

        return view('organizations.manage');
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['organizations.add'])) {
            abort(403);
        }

        return view('organizations.add', [
            'locations' => Location::with('region.country')->where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function addressSearch(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'organizations.manage', ['organizations.view', 'organizations.add', 'organizations.edit', 'organizations.delete', 'organizations.manage'])) {
            abort(403);
        }

        $query = trim((string) $request->input('q', ''));
        $location = trim((string) $request->input('location', ''));

        if (mb_strlen($query) < 3) {
            return response()->json(['results' => []]);
        }

        $searchQuery = $this->buildAddressLookupQuery($query, $location);

        $response = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Foodshop') . '/1.0',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 5,
                'q' => $searchQuery,
            ]);

        if (! $response->ok()) {
            return response()->json(['results' => []], 200);
        }

        $results = collect($response->json() ?: [])
            ->filter(fn ($item) => is_array($item))
            ->map(function ($item) {
                return [
                    'title' => (string) ($item['name'] ?? strtok((string) ($item['display_name'] ?? ''), ',')),
                    'label' => (string) ($item['display_name'] ?? ''),
                    'latitude' => (string) ($item['lat'] ?? ''),
                    'longitude' => (string) ($item['lon'] ?? ''),
                ];
            })
            ->filter(fn ($item) => $item['label'] !== '')
            ->values()
            ->all();

        return response()->json(['results' => $results]);
    }

    public function geocodeAddress(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'organizations.manage', ['organizations.view', 'organizations.add', 'organizations.edit', 'organizations.delete', 'organizations.manage'])) {
            abort(403);
        }

        $address = trim((string) $request->input('address', ''));
        $location = trim((string) $request->input('location', ''));

        if ($address === '' && $location === '') {
            return response()->json(['result' => null]);
        }

        $searchQuery = $this->buildAddressLookupQuery($address, $location);

        $response = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Foodshop') . '/1.0',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'limit' => 1,
                'q' => $searchQuery,
            ]);

        if (! $response->ok()) {
            return response()->json(['result' => null], 200);
        }

        $item = collect($response->json() ?: [])->first();
        if (! is_array($item)) {
            return response()->json(['result' => null], 200);
        }

        return response()->json([
            'result' => [
                'address' => (string) ($item['display_name'] ?? $address),
                'latitude' => (string) ($item['lat'] ?? ''),
                'longitude' => (string) ($item['lon'] ?? ''),
            ],
        ]);
    }

    public function reverseGeocodeAddress(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'organizations.manage', ['organizations.view', 'organizations.add', 'organizations.edit', 'organizations.delete', 'organizations.manage'])) {
            abort(403);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $response = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Foodshop') . '/1.0',
            ])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
            ]);

        if (! $response->ok()) {
            return response()->json(['result' => null], 200);
        }

        $item = $response->json();
        if (! is_array($item)) {
            return response()->json(['result' => null], 200);
        }

        return response()->json([
            'result' => [
                'address' => (string) ($item['display_name'] ?? ''),
                'latitude' => (string) ($item['lat'] ?? $validated['latitude']),
                'longitude' => (string) ($item['lon'] ?? $validated['longitude']),
            ],
        ]);
    }

    private function buildAddressLookupQuery(string $address, string $location = ''): string
    {
        $address = trim($address);
        $location = trim($location);

        if ($address === '') {
            return $location;
        }

        // If the user already typed a full address, avoid over-constraining
        // the open geocoder with an extra duplicated location string.
        if (str_contains($address, ',') || preg_match('/\d{3,}/', $address) || mb_strlen($address) >= 18) {
            return $address;
        }

        return collect([$address, $location])
            ->filter(fn ($value) => $value !== '')
            ->implode(', ');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['organizations.add'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name',
            'location_id' => 'required|exists:locations,id',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|boolean',
        ]);

        Organization::create($validated);

        return redirect()->route('organizations.manage')
            ->with('success', __('organization_created_successfully'));
    }

    public function edit(Organization $organization)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['organizations.edit'])) {
            abort(403);
        }

        return view('organizations.edit', [
            'organization' => $organization,
            'locations' => Location::with('region.country')->where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['organizations.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            'location_id' => 'required|exists:locations,id',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|boolean',
        ]);

        $organization->update($validated);

        return redirect()->route('organizations.manage')
            ->with('success', __('organization_updated_successfully'));
    }

    public function destroy(Organization $organization)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['organizations.delete'])) {
            abort(403);
        }

        $organization->delete();

        return redirect()->route('organizations.manage')
            ->with('success', __('organization_deleted_successfully'));
    }
}
