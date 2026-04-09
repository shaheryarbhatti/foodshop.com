<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the currencies.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'currencies.manage', ['currencies.view', 'currencies.add', 'currencies.edit', 'currencies.delete', 'currencies.manage'])) {
            abort(403);
        }

        if (request()->ajax()) {
            $query = Currency::query();

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('status', function ($currency) {
                    return $currency->is_active
                        ? '<span class="badge rounded-pill bg-success">Active</span>'
                        : '<span class="badge rounded-pill bg-secondary">Inactive</span>';
                })
                ->addColumn('action', function ($currency) use ($user) {
                    $canEdit = $this->userCanAny($user, ['currencies.edit']);
                    $canDelete = $this->userCanAny($user, ['currencies.delete']);
                    $edit = $canEdit
                        ? '<a href="' . route('currencies.edit', $currency->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>'
                        : '';
                    $delete = $canDelete ? '<form action="' . route('currencies.destroy', $currency->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                          </form>' : '';

                    if (! $edit && ! $delete) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="action d-flex gap-2">' . $edit . $delete . '</div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $currencies = Currency::orderBy('name')->get();
        $baseCurrencyCode = Setting::get('base_currency_code', 'IDR');
        return view('currencies.manage', compact('currencies', 'baseCurrencyCode'));
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('currencies.add');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // If setting as active, deactivate others
        if ($request->is_active) {
            Currency::where('is_active', true)->update(['is_active' => false]);
        }

        Currency::create($validated);

        return redirect()->route('currencies.manage')
            ->with('success', 'Currency created successfully.');
    }

    /**
     * Display the specified currency.
     */
    public function show(Currency $currency)
    {
        return view('currencies.show', compact('currency'));
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code,' . $currency->id,
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // If setting as active, deactivate others
        if ($request->is_active && !$currency->is_active) {
            Currency::where('is_active', true)->update(['is_active' => false]);
        }

        $currency->update($validated);

        return redirect()->route('currencies.manage')
            ->with('success', 'Currency updated successfully.');
    }

    public function setBase(Request $request)
    {
        $validated = $request->validate([
            'base_currency_id' => 'required|exists:currencies,id',
        ]);

        $newBase = Currency::findOrFail($validated['base_currency_id']);
        $newBaseRateOld = (float) $newBase->exchange_rate;

        if ($newBaseRateOld <= 0) {
            return redirect()->back()->with('error', 'Base currency exchange rate must be greater than 0.');
        }

        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            $newRate = (float) $currency->exchange_rate / $newBaseRateOld;
            $currency->exchange_rate = $newRate > 0 ? $newRate : 1;
            $currency->save();
        }

        // Ensure base currency rate is 1
        $newBase->exchange_rate = 1;
        $newBase->save();

        Setting::updateOrCreate(
            ['key' => 'base_currency_code'],
            ['value' => $newBase->code]
        );

        return redirect()->back()->with('success', 'Base currency updated successfully.');
    }

    /**
     * Remove the specified currency from storage.
     */
    public function destroy(Currency $currency)
    {
        // Prevent deleting active currency
        if ($currency->is_active) {
            return redirect()->back()->with('error', 'Cannot delete active currency.');
        }

        $currency->delete();

        return redirect()->route('currencies.manage')
            ->with('success', 'Currency deleted successfully.');
    }
}
