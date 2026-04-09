<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'customers.manage', ['customers.view', 'customers.add', 'customers.edit', 'customers.delete', 'customers.manage'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Customer::query()->select('customers.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('customer_name', fn ($customer) => e($customer->full_name))
                ->addColumn('customer_type_badge', function ($customer) {
                    $label = $customer->customer_type === 'company' ? __('company') : __('individual');
                    $class = $customer->customer_type === 'company' ? 'bg-info' : 'bg-primary';

                    return '<span class="badge rounded-pill ' . $class . '">' . $label . '</span>';
                })
                ->addColumn('company_label', fn ($customer) => e($customer->company_name ?: '-'))
                ->addColumn('status_badge', function ($customer) {
                    return $customer->status
                        ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>'
                        : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>';
                })
                ->addColumn('action', function ($customer) use ($user) {
                    $canView = $this->userCanAny($user, ['customers.view']);
                    $canEdit = $this->userCanAny($user, ['customers.edit']);
                    $canDelete = $this->userCanAny($user, ['customers.delete']);

                    $view = $canView ? '<a href="' . route('customers.show', $customer->id) . '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>' : '';
                    $edit = $canEdit ? '<a href="' . route('customers.edit', $customer->id) . '" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>' : '';
                    $delete = $canDelete ? '<form action="' . route('customers.destroy', $customer->id) . '" method="POST" style="display:inline;" class="js-confirm-delete" data-btn-gap="true">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></form>' : '';

                    return $view || $edit || $delete ? '<div class="action d-flex gap-2">' . $view . $edit . $delete . '</div>' : '<span class="text-muted">-</span>';
                })
                ->rawColumns(['customer_type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('customers.manage');
    }

    public function create()
    {
        $this->authorizeAction('customers.add');

        return view('customers.add');
    }

    public function store(Request $request)
    {
        $this->authorizeAction('customers.add');

        $validated = $this->validateCustomer($request);
        $validated['password'] = Hash::make($validated['password']);

        Customer::create($validated);

        return redirect()->route('customers.manage')->with('success', __('customer_created_successfully'));
    }

    public function show(Customer $customer)
    {
        $this->authorizeAction('customers.view');

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeAction('customers.edit');

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeAction('customers.edit');

        $validated = $this->validateCustomer($request, $customer, true);
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $customer->update($validated);

        return redirect()->route('customers.manage')->with('success', __('customer_updated_successfully'));
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeAction('customers.delete');

        $customer->delete();

        return redirect()->route('customers.manage')->with('success', __('customer_deleted_successfully'));
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, [$permission])) {
            abort(403);
        }
    }

    private function validateCustomer(Request $request, ?Customer $customer = null, bool $updating = false): array
    {
        $customerId = $customer?->id;

        $rules = [
            'customer_type' => ['required', Rule::in(['individual', 'company'])],
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'company_name' => 'nullable|string|max:180',
            'email' => 'required|email|max:190|unique:customers,email,' . $customerId,
            'phone' => 'required|string|max:40',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:120',
            'postal_code' => 'required|string|max:40',
            'country' => 'required|string|max:120',
            'status' => 'required|boolean',
        ];

        $rules['password'] = $updating
            ? 'nullable|string|min:6|max:255'
            : 'required|string|min:6|max:255';

        $validated = $request->validate($rules);
        if (($validated['customer_type'] ?? 'individual') !== 'company') {
            $validated['company_name'] = null;
        }

        return $validated;
    }
}
