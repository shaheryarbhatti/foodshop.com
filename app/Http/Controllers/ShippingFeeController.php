<?php

namespace App\Http\Controllers;

use App\Models\ShippingFee;
use App\Models\Tax;
use Illuminate\Http\Request;

class ShippingFeeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'shipping.manage', ['shipping.view', 'shipping.add', 'shipping.edit', 'shipping.delete', 'shipping.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            return datatables()->of(ShippingFee::with('tax')->select('shipping_fees.*'))
                ->addIndexColumn()
                ->addColumn('bulk_select', fn ($shipping) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $shipping->id . '">')
                ->addColumn('tax_name', fn ($shipping) => e(optional($shipping->tax)->title ?: '-'))
                ->addColumn('fee_label', fn ($shipping) => number_format((float) $shipping->fee, 2))
                ->addColumn('min_order_amount', fn ($shipping) => number_format((float) $shipping->min_order_amount, 2))
                ->addColumn('status_badge', function ($shipping) {
                    return $shipping->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($shipping) use ($user) {
                    $canEdit = $this->userCanAny($user, ['shipping.edit']);
                    $canDelete = $this->userCanAny($user, ['shipping.delete']);
                    $edit = $canEdit ? '<a href="' . route('shipping.edit', $shipping->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('shipping.destroy', $shipping->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';
                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['bulk_select', 'status_badge', 'action'])
                ->make(true);
        }

        return view('shipping.manage');
    }

    public function create()
    {
        $this->authorizeAction('shipping.add');
        return view('shipping.add', ['taxes' => Tax::where('status', true)->orderBy('title')->get()]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('shipping.add');
        ShippingFee::create($this->validateShipping($request));
        return redirect()->route('shipping.manage')->with('success', __('shipping_fee_created_successfully'));
    }

    public function edit(ShippingFee $shipping)
    {
        $this->authorizeAction('shipping.edit');
        return view('shipping.edit', ['shipping' => $shipping, 'taxes' => Tax::where('status', true)->orderBy('title')->get()]);
    }

    public function update(Request $request, ShippingFee $shipping)
    {
        $this->authorizeAction('shipping.edit');
        $shipping->update($this->validateShipping($request));
        return redirect()->route('shipping.manage')->with('success', __('shipping_fee_updated_successfully'));
    }

    public function destroy(ShippingFee $shipping)
    {
        $this->authorizeAction('shipping.delete');
        $shipping->delete();
        return redirect()->route('shipping.manage')->with('success', __('shipping_fee_deleted_successfully'));
    }

    public function destroyAll()
    {
        $this->authorizeAction('shipping.delete');

        $shippingIds = request()->input('selected_ids', []);
        if (! is_array($shippingIds) || empty($shippingIds)) {
            return redirect()->route('shipping.manage')->with('error', __('select_records_to_delete'));
        }

        ShippingFee::query()->whereIn('id', $shippingIds)->delete();

        return redirect()->route('shipping.manage')->with('success', __('all_shipping_fees_deleted_successfully'));
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }

    private function validateShipping(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'tax_id' => 'nullable|exists:taxes,id',
            'fee' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'condition_type' => 'required|in:less_than,equal_to,greater_than,between',
            'distance_value' => 'required|string|max:255',
            'maximum_order_amount' => 'nullable|numeric|min:0',
            'delivery_type' => 'required|in:delivery,pickup',
            'status' => 'required|boolean',
        ]);
    }
}
