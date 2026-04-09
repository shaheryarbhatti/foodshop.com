<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'taxes.manage', ['taxes.view', 'taxes.add', 'taxes.edit', 'taxes.delete', 'taxes.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            return datatables()->of(Tax::query()->select('taxes.*'))
                ->addIndexColumn()
                ->addColumn('bulk_select', fn ($tax) => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $tax->id . '">')
                ->addColumn('amount_label', fn ($tax) => number_format((float) $tax->amount, 2))
                ->addColumn('type_label', fn ($tax) => e($tax->calculation_type === 'percentage' ? __('percentage') : __('fixed_amount')))
                ->addColumn('status_badge', function ($tax) {
                    return $tax->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($tax) use ($user) {
                    $canEdit = $this->userCanAny($user, ['taxes.edit']);
                    $canDelete = $this->userCanAny($user, ['taxes.delete']);
                    $edit = $canEdit ? '<a href="' . route('taxes.edit', $tax->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('taxes.destroy', $tax->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';
                    return $edit || $delete ? '<div class="action d-flex gap-2">' . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['bulk_select', 'status_badge', 'action'])
                ->make(true);
        }

        return view('taxes.manage');
    }

    public function create()
    {
        $this->authorizeAction('taxes.add');
        return view('taxes.add');
    }

    public function store(Request $request)
    {
        $this->authorizeAction('taxes.add');
        Tax::create($this->validateTax($request));
        return redirect()->route('taxes.manage')->with('success', __('tax_created_successfully'));
    }

    public function edit(Tax $tax)
    {
        $this->authorizeAction('taxes.edit');
        return view('taxes.edit', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        $this->authorizeAction('taxes.edit');
        $tax->update($this->validateTax($request));
        return redirect()->route('taxes.manage')->with('success', __('tax_updated_successfully'));
    }

    public function destroy(Tax $tax)
    {
        $this->authorizeAction('taxes.delete');
        $tax->delete();
        return redirect()->route('taxes.manage')->with('success', __('tax_deleted_successfully'));
    }

    public function destroyAll()
    {
        $this->authorizeAction('taxes.delete');

        $taxIds = request()->input('selected_ids', []);
        if (! is_array($taxIds) || empty($taxIds)) {
            return redirect()->route('taxes.manage')->with('error', __('select_records_to_delete'));
        }

        Tax::query()->whereIn('id', $taxIds)->delete();

        return redirect()->route('taxes.manage')->with('success', __('all_taxes_deleted_successfully'));
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }

    private function validateTax(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'calculation_type' => 'required|in:fixed,percentage',
            'status' => 'required|boolean',
        ]);
    }
}
