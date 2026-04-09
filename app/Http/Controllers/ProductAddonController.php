<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAddon;
use Illuminate\Http\Request;

class ProductAddonController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'addons.manage', ['addons.view', 'addons.add', 'addons.edit', 'addons.delete', 'addons.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = ProductAddon::with(['products', 'values'])->select('product_addons.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('bulk_select', fn ($addon) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $addon->id . '">')
                ->addColumn('product_name', function ($addon) {
                    if ($addon->products->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    $total = $addon->products->count();
                    $displayLimit = 2; // Show first 2 products

                    $displayProducts = $addon->products->take($displayLimit);
                    $html = $displayProducts->map(function($product) {
                        return '<span class="badge bg-light text-dark border me-1 mb-1">' . e($product->title) . '</span>';
                    })->implode('');

                    if ($total > $displayLimit) {
                        $remaining = $total - $displayLimit;
                        $remainingTitles = e($addon->products->slice($displayLimit)->pluck('title')->implode(', '));
                        $html .= '<span class="badge bg-primary mb-1" title="' . $remainingTitles . '">+' . $remaining . ' ' . __('more') . '</span>';
                    }

                    return '<div class="product-badges-container d-flex flex-wrap">' . $html . '</div>';
                })
                ->addColumn('selection_label', function($addon) {
                    if ($addon->selection_type === 'multiple') return __('multiple_select');
                    if ($addon->selection_type === 'listing') return __('listing_select');
                    return __('single_select');
                })
                ->addColumn('values_list', function ($addon) {
                    if ($addon->values->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    return $addon->values->map(function ($value) {
                        $price = $value->price !== null ? ' (' . number_format((float) $value->price, 2) . ')' : '';
                        return '<span class="badge bg-light text-dark border me-1 mb-1">' . e($value->title . $price) . '</span>';
                    })->implode(' ');
                })
                ->addColumn('status_badge', function ($addon) {
                    return $addon->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($addon) use ($user) {
                    $canEdit = $this->userCanAny($user, ['addons.edit']);
                    $canDelete = $this->userCanAny($user, ['addons.delete']);
                    $edit = $canEdit ? '<a href="' . route('addons.edit', $addon->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('addons.destroy', $addon->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';

                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['bulk_select', 'product_name', 'values_list', 'status_badge', 'action'])
                ->make(true);
        }

        return view('addons.manage');
    }

    public function create()
    {
        $this->authorizeAction('addons.add');

        return view('addons.add', ['products' => Product::where('status', true)->orderBy('title')->get()]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('addons.add');

        $validated = $this->validateAddon($request);

        $addon = ProductAddon::create([
            'title' => $validated['title'],
            'selection_type' => $validated['selection_type'],
            'status' => $validated['status'],
        ]);

        $addon->products()->sync($validated['product_ids']);

        $this->syncValues($addon, $request);

        return redirect()->route('addons.manage')->with('success', __('addon_created_successfully'));
    }

    public function edit(ProductAddon $addon)
    {
        $this->authorizeAction('addons.edit');
        $addon->load('values');

        return view('addons.edit', [
            'addon' => $addon,
            'products' => Product::where('status', true)->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, ProductAddon $addon)
    {
        $this->authorizeAction('addons.edit');

        $validated = $this->validateAddon($request);

        $addon->update([
            'title' => $validated['title'],
            'selection_type' => $validated['selection_type'],
            'status' => $validated['status'],
        ]);

        $addon->products()->sync($validated['product_ids']);

        $this->syncValues($addon, $request, true);

        return redirect()->route('addons.manage')->with('success', __('addon_updated_successfully'));
    }

    public function destroy(ProductAddon $addon)
    {
        $this->authorizeAction('addons.delete');
        $addon->delete();

        return redirect()->route('addons.manage')->with('success', __('addon_deleted_successfully'));
    }

    public function destroyAll()
    {
        $this->authorizeAction('addons.delete');

        $addonIds = request()->input('selected_ids', []);
        if (! is_array($addonIds) || empty($addonIds)) {
            return redirect()->route('addons.manage')->with('error', __('select_records_to_delete'));
        }

        ProductAddon::query()->whereIn('id', $addonIds)->delete();

        return redirect()->route('addons.manage')->with('success', __('all_addons_deleted_successfully'));
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }

    private function validateAddon(Request $request): array
    {
        return $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'title' => 'required|string|max:255',
            'selection_type' => 'required|in:single,multiple,listing',
            'status' => 'required|boolean',
            'value_titles' => 'required|array|min:1',
            'value_titles.*' => 'required|string|max:255',
            'value_prices' => 'nullable|array',
            'value_prices.*' => 'nullable|numeric|min:0',
            'value_statuses' => 'nullable|array',
            'value_ids' => 'nullable|array',
        ]);
    }

    private function syncValues(ProductAddon $addon, Request $request, bool $isUpdate = false): void
    {
        $titles = $request->input('value_titles', []);
        $prices = $request->input('value_prices', []);
        $statuses = $request->input('value_statuses', []);
        $ids = $request->input('value_ids', []);

        if ($isUpdate) {
            // Delete only the values that were removed in the UI
            $keepIds = array_filter($ids);
            $addon->values()->whereNotIn('id', $keepIds)->delete();
        }

        foreach ($titles as $index => $title) {
            $trimmedTitle = trim((string) $title);
            if ($trimmedTitle === '') continue;

            $data = [
                'title' => $trimmedTitle,
                'price' => $prices[$index] !== null && $prices[$index] !== '' ? $prices[$index] : null,
                'status' => (int) ($statuses[$index] ?? 1) === 1,
            ];

            // If we have an ID and it belongs to this addon, update it to preserve the ID
            if ($isUpdate && !empty($ids[$index])) {
                $addon->values()->where('id', $ids[$index])->update($data);
            } else {
                $addon->values()->create($data);
            }
        }
    }
}
