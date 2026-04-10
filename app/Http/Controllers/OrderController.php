<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.view', 'orders.edit'])) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = Order::with(['organization.location', 'customer', 'items', 'driver'])
                ->select('orders.*')
                ->orderByDesc('orders.created_at')
                ->orderByDesc('orders.id');

            $statusFilter = (string) $request->input('status_filter', '');
            if ($statusFilter !== '' && array_key_exists($statusFilter, Order::statusOptions())) {
                $query->where('orders.order_status', $statusFilter);
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('order_reference', function (Order $order) {
                    return '<div class="fw-semibold text-dark">' . e($order->order_number ?: ('#' . $order->id)) . '</div>'
                        . '<div class="small text-muted">' . e(optional($order->created_at)->format('d M Y, h:i A') ?: '-') . '</div>';
                })
                ->addColumn('customer_summary', function (Order $order) {
                    return '<div class="fw-semibold text-dark">' . e(trim($order->first_name . ' ' . $order->last_name)) . '</div>'
                        . '<div class="small text-muted">' . e($order->email) . '</div>'
                        . '<div class="small text-muted">' . e($order->phone) . '</div>';
                })
                ->addColumn('branch_summary', function (Order $order) {
                    $branchName = optional($order->organization)->name ?: __('not_available');
                    $branchLocation = optional(optional($order->organization)->location)->name;
                    $driverName = optional($order->driver)->name;

                    return '<div class="fw-semibold text-dark">' . e($branchName) . '</div>'
                        . '<div class="small text-muted">' . e($branchLocation ?: ($order->city ?: '-')) . '</div>'
                        . '<div class="small text-muted">' . e($driverName ? ('Driver: ' . $driverName) : 'Driver: Unassigned') . '</div>';
                })
                ->addColumn('items_summary', function (Order $order) {
                    $items = $order->items;
                    if ($items->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }

                    $firstTwo = $items->take(2)->map(function ($item) {
                        return e($item->quantity . ' x ' . $item->title);
                    })->implode('<br>');

                    $remaining = $items->count() - 2;
                    if ($remaining > 0) {
                        $firstTwo .= '<div class="small text-muted mt-1">+' . e((string) $remaining) . ' ' . e(__('more_items')) . '</div>';
                    }

                    return '<div class="small text-dark">' . $firstTwo . '</div>';
                })
                ->addColumn('order_type_badge', function (Order $order) {
                    $isDelivery = $order->order_type === 'delivery';

                    return '<span class="badge rounded-pill ' . ($isDelivery ? 'bg-primary' : 'bg-info') . '">'
                        . e($isDelivery ? __('frontend_delivery') : __('frontend_pick_up'))
                        . '</span>';
                })
                ->addColumn('payment_summary', function (Order $order) {
                    return '<div class="fw-semibold text-dark">' . e($this->paymentMethodTitle($order->payment_method)) . '</div>'
                        . '<div class="small text-muted">' . e(ucfirst(str_replace('_', ' ', $order->payment_status ?: 'pending'))) . '</div>';
                })
                ->addColumn('status_selector', function (Order $order) use ($user) {
                    return $this->orderStatusBadge($order->order_status);
                })
                ->addColumn('grand_total_display', function (Order $order) {
                    return '<div class="fw-semibold text-dark">' . e($this->money($order->payment_currency, $order->grand_total)) . '</div>'
                        . '<div class="small text-muted">' . e(__('subtotal')) . ': ' . e($this->money($order->payment_currency, $order->subtotal)) . '</div>';
                })
                ->addColumn('action', function (Order $order) use ($user) {
                    $canView = $this->userCanAny($user, ['orders.view']);
                    $canEditStatus = $user && $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit']);
                    $canDelete = $this->userCanAny($user, ['orders.delete']);
                    $statusButton = $canEditStatus
                        ? '<button type="button" class="btn btn-sm js-open-order-status-modal" '
                            . 'style="background-color: #10b981; color: white; border-color: #10b981;" '
                            . 'onmouseover="this.style.backgroundColor=\'#10b981\'" '
                            . 'onmouseout="this.style.backgroundColor=\'#10b981\'" '
                            . 'data-url="' . e(route('orders.updateStatus', $order)) . '" '
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'data-current-status="' . e($order->order_status) . '" '
                            . 'title="' . __('update_status') . '">'
                            . '<i class="fa fa-arrows-rotate"></i></button>'
                        : '';
                    $deleteButton = $canDelete
                        ? '<form action="' . route('orders.destroy', $order) . '" method="POST" style="display:inline;" class="js-confirm-delete">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="btn btn-danger btn-sm" title="' . __('delete') . '"><i class="fa fa-trash"></i></button></form>'
                        : '';
                    $editOrder = $canEditStatus ? '<a href="' . route('orders.edit', $order) . '" class="btn btn-dark btn-sm" title="' . __('edit_order') . '"><i class="fa fa-pen-to-square"></i></a>' : '';
                    $invoice = $canView
                        ? '<button type="button" class="btn btn-info btn-sm js-open-order-invoice" data-url="' . e(route('orders.invoice', $order)) . '" data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" title="' . __('view_invoice') . '"><i class="fa fa-file-invoice"></i></button>'
                        : '';
                    $pdf = $canView
                        ? '<a href="' . route('orders.invoice.download', $order) . '" class="btn btn-primary btn-sm" title="' . __('download_pdf') . '"><i class="fa fa-file-pdf"></i></a>'
                        : '';

                    $canShowRoute = filled($order->customer_latitude)
                        && filled($order->customer_longitude)
                        && filled(optional($order->organization)->latitude)
                        && filled(optional($order->organization)->longitude);

                    $routeButton = $canView
                        ? '<button type="button" class="btn btn-warning btn-sm js-show-order-route" '
                            . ($canShowRoute ? '' : 'disabled ')
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'data-branch-name="' . e(optional($order->organization)->name ?: '-') . '" '
                            . 'data-branch-address="' . e(optional($order->organization)->address ?: '-') . '" '
                            . 'data-branch-latitude="' . e((string) (optional($order->organization)->latitude ?: '')) . '" '
                            . 'data-branch-longitude="' . e((string) (optional($order->organization)->longitude ?: '')) . '" '
                            . 'data-customer-name="' . e(trim($order->first_name . ' ' . $order->last_name)) . '" '
                            . 'data-customer-address="' . e($order->address . ', ' . $order->city . ', ' . $order->country) . '" '
                            . 'data-customer-latitude="' . e((string) ($order->customer_latitude ?: '')) . '" '
                            . 'data-customer-longitude="' . e((string) ($order->customer_longitude ?: '')) . '" '
                            . 'data-distance="' . e((string) ($order->delivery_distance_km ?: '0')) . '" '
                            . 'data-google-url="' . e($this->googleMapsRouteUrl($order) ?: '') . '" '
                            . 'title="' . __('view_route') . '">'
                            . '<i class="fa fa-route"></i></button>'
                        : '';
                    $assignDriverButton = $canEditStatus
                        ? '<button type="button" class="btn btn-primary btn-sm js-open-driver-assign-modal" '
                            . 'data-order-id="' . e((string) $order->id) . '" '
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'title="Assign Driver"><i class="fa fa-user-check"></i></button>'
                        : '';
                    $changeBranchButton = $canEditStatus
                        ? '<button type="button" class="btn btn-secondary btn-sm js-open-branch-change-modal" '
                            . 'data-order-id="' . e((string) $order->id) . '" '
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'title="' . __('change_branch') . '"><i class="fa fa-code-branch"></i></button>'
                        : '';

                    $actions = $statusButton . $editOrder . $invoice . $pdf . $routeButton . $assignDriverButton . $changeBranchButton . $deleteButton;

                    return $actions
                        ? '<div class="action d-flex gap-2">' . $actions . '</div>'
                        : '<span class="text-muted">-</span>';
                })
                ->rawColumns([
                    'order_reference',
                    'customer_summary',
                    'branch_summary',
                    'items_summary',
                    'order_type_badge',
                    'payment_summary',
                    'status_selector',
                    'grand_total_display',
                    'action',
                ])
                ->make(true);
        }

        return view('orders.manage', [
            'statusCards' => $this->statusCards(),
            'statusOptions' => Order::statusOptions(),
        ]);
    }

    public function edit(Order $order): View
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $order->load(['items' => function($q) {
            $q->select('*');
        }, 'customer']);

        // Ensure addons are decoded if not automatically cast in the Model
        $order->items->each(function($item) {
            $item->addons = is_string($item->addons) ? json_decode($item->addons, true) : $item->addons;
        });

        $products = \App\Models\Product::with(['addons.values'])->where('status', true)->get();

        return view('orders.edit', [
            'order' => $order,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Order $order, \App\Services\OrderPricingService $pricingService)
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'items_json' => 'required|json',
        ]);

        $data = json_decode($validated['items_json'], true);
        $newItemsData = $data['new_items'] ?? [];
        $deletedIds = $data['deleted_ids'] ?? [];
        $existingAddonsData = $data['existing_addons'] ?? [];

        return DB::transaction(function () use ($order, $newItemsData, $deletedIds, $existingAddonsData, $pricingService) {
            $existingAddonTotal = 0;
            $existingAddonTaxTotal = 0;
            if (!empty($existingAddonsData)) {
                foreach ($existingAddonsData as $entry) {
                    $item = OrderItem::where('order_id', $order->id)->where('id', $entry['id'] ?? null)->first();
                    if (!$item) {
                        continue;
                    }

                    $additionAmount = max(0, (float) ($entry['amount'] ?? 0));
                    $additionTax = max(0, (float) ($entry['tax'] ?? 0));
                    $existingAddonTotal += $additionAmount;
                    $existingAddonTaxTotal += $additionTax;

                    $currentAddons = $item->addons;
                    if (is_string($currentAddons)) {
                        $currentAddons = json_decode($currentAddons, true) ?: [];
                    }

                    $item->addons = array_values(array_merge($currentAddons ?: [], $entry['addons'] ?? []));
                    $item->addition_amount = $additionAmount;
                    $item->addition_tax = $additionTax;
                    $item->save();
                }
            }

            // Delete removed items
            if (!empty($deletedIds)) {
                $order->items()->whereIn('id', $deletedIds)->delete();
            }

            // Create new items
            foreach ($newItemsData as $newItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $newItem['product_id'],
                    'title' => $newItem['title'],
                    'quantity' => $newItem['quantity'],
                    'unit_price' => $newItem['unit_price'],
                    'line_total' => $newItem['line_total'],
                    'addons' => $newItem['addons'] ?? null,
                    'addition_amount' => $newItem['addition_amount'] ?? 0,
                    'addition_tax' => $newItem['addition_tax'] ?? 0,
                ]);
            }

            // Recalculate totals
            $updatedItems = $order->items()->get()->toArray();
            $totals = $pricingService->calculate(
                $updatedItems,
                $order->order_type,
                $order->customer
            );

            $order->update([
                'subtotal' => $totals['subtotal'] + $existingAddonTotal,
                'shipping_costs' => $totals['shipping_costs'],
                'vat_amount' => $totals['vat_amount'] + $existingAddonTaxTotal,
                'grand_total' => $totals['grand_total'] + $existingAddonTotal + $existingAddonTaxTotal,
                'admin_updated' => true,
            ]);

            $message = __('order_updated_successfully');
            if ($order->grand_total > $order->amount_paid) {
                \App\Services\OrderEmailService::sendStatusEmail($order, 'order_balance_payment');
                $message .= ' ' . __('payment_link_sent_to_customer');
            }

            return redirect()->route('orders.manage')->with('success', $message);
        });
    }

    public function destroy(Order $order)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['orders.delete'])) {
            abort(403);
        }

        DB::transaction(function () use ($order) {
            // Delete related order items
            $order->items()->delete();
            // Delete the order itself
            $order->delete();
        });

        return redirect()->route('orders.manage')->with('success', __('order_deleted_successfully'));
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'order_status' => ['required', Rule::in(array_keys(Order::statusOptions()))],
        ]);

        $order->update([
            'order_status' => $validated['order_status'],
        ]);

        return response()->json([
            'message' => __('order_status_updated_successfully'),
            'status_badge' => $this->orderStatusBadge($order->order_status),
        ]);
    }

    public function availableDrivers(Order $order): JsonResponse
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $drivers = $this->branchDrivers($order)
            ->get(['users.id', 'users.name', 'users.email'])
            ->map(fn (User $driver) => [
                'id' => $driver->id,
                'name' => $driver->name,
                'email' => $driver->email,
            ])
            ->values()
            ->all();

        return response()->json([
            'drivers' => $drivers,
            'assigned_driver_id' => $order->driver_id,
        ]);
    }

    public function assignDriver(Request $request, Order $order): JsonResponse
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $driver = $this->branchDrivers($order)
            ->where('users.id', $validated['driver_id'])
            ->first();

        if (! $driver) {
            return response()->json([
                'message' => 'Selected driver is not valid for this branch.',
            ], 422);
        }

        $order->update([
            'driver_id' => $driver->id,
        ]);

        return response()->json([
            'message' => 'Driver assigned successfully.',
            'driver_name' => $driver->name,
        ]);
    }

    public function availableBranches(Order $order): JsonResponse
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $branches = Organization::query()
            ->where('status', true)
            ->with('location')
            ->orderBy('name')
            ->get(['id', 'name', 'location_id', 'address'])
            ->map(fn (Organization $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => optional($branch->location)->name,
                'address' => $branch->address,
            ])
            ->values()
            ->all();

        return response()->json([
            'branches' => $branches,
            'selected_branch_id' => $order->organization_id,
        ]);
    }

    public function updateBranch(Request $request, Order $order): JsonResponse
    {
        $user = auth()->user();
        if ($user && ! $this->canAccessRoute($user, 'orders.manage', ['orders.manage', 'orders.edit'])) {
            abort(403);
        }

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ]);

        $branch = Organization::query()
            ->where('status', true)
            ->find($validated['organization_id']);

        if (! $branch) {
            return response()->json([
                'message' => __('selected_branch_is_not_available'),
            ], 422);
        }

        $currentBranchId = (int) $order->organization_id;
        $nextBranchId = (int) $branch->id;

        $order->organization_id = $nextBranchId;
        if ($currentBranchId !== $nextBranchId) {
            $order->driver_id = null;
        }
        $order->save();

        return response()->json([
            'message' => __('order_branch_updated_successfully'),
            'branch_name' => $branch->name,
            'driver_cleared' => $currentBranchId !== $nextBranchId,
        ]);
    }

    public function invoice(Order $order): View
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['orders.view'])) {
            abort(403);
        }

        return view('orders.invoice', $this->invoiceViewData($order));
    }

    public function downloadInvoice(Order $order)
    {
        $user = auth()->user();
        if ($user && ! $this->userCanAny($user, ['orders.view'])) {
            abort(403);
        }

        $pdf = Pdf::loadView('orders.invoice-pdf', $this->invoiceViewData($order));

        return $pdf->setPaper('a4')->download(($order->order_number ?: ('order-' . $order->id)) . '.pdf');
    }

    private function statusCards(): array
    {
        $statusCounts = Order::query()
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $totalOrders = Order::query()->count();

        return [
            ['key' => 'all', 'title' => __('total_orders'), 'count' => $totalOrders, 'icon' => 'fa-box', 'theme' => 'order-card-total'],
            ['key' => Order::STATUS_PENDING_PAYMENT, 'title' => __('pending_payment'), 'count' => (int) ($statusCounts[Order::STATUS_PENDING_PAYMENT] ?? 0), 'icon' => 'fa-clock', 'theme' => 'order-card-pending'],
            ['key' => Order::STATUS_PROCESSING, 'title' => __('processing'), 'count' => (int) ($statusCounts[Order::STATUS_PROCESSING] ?? 0), 'icon' => 'fa-box-open', 'theme' => 'order-card-processing'],
            ['key' => Order::STATUS_SHIPPED, 'title' => __('shipped'), 'count' => (int) ($statusCounts[Order::STATUS_SHIPPED] ?? 0), 'icon' => 'fa-truck-fast', 'theme' => 'order-card-shipped'],
            ['key' => Order::STATUS_DELIVERED, 'title' => __('delivered'), 'count' => (int) ($statusCounts[Order::STATUS_DELIVERED] ?? 0), 'icon' => 'fa-circle-check', 'theme' => 'order-card-delivered'],
            ['key' => Order::STATUS_CANCELLED, 'title' => __('cancelled'), 'count' => (int) ($statusCounts[Order::STATUS_CANCELLED] ?? 0), 'icon' => 'fa-boxes-packing', 'theme' => 'order-card-cancelled'],
            ['key' => Order::STATUS_RETURNED, 'title' => __('returned'), 'count' => (int) ($statusCounts[Order::STATUS_RETURNED] ?? 0), 'icon' => 'fa-rotate-left', 'theme' => 'order-card-returned'],
            ['key' => Order::STATUS_FAILED, 'title' => __('failed'), 'count' => (int) ($statusCounts[Order::STATUS_FAILED] ?? 0), 'icon' => 'fa-circle-xmark', 'theme' => 'order-card-failed'],
        ];
    }

    private function invoiceViewData(Order $order): array
    {
        $order->loadMissing(['items', 'organization.location', 'customer']);

        return [
            'order' => $order,
            'paymentMethodTitle' => $this->paymentMethodTitle($order->payment_method),
            'googleMapsRouteUrl' => $this->googleMapsRouteUrl($order),
        ];
    }

    private function paymentMethodTitle(?string $code): string
    {
        $method = collect(Setting::paymentMethods())
            ->first(fn ($item) => is_array($item) && ($item['code'] ?? null) === $code);

        return $method['title'] ?? Str::headline(str_replace('_', ' ', (string) $code));
    }

    private function money(?string $currencyCode, float|int|string|null $amount): string
    {
        return strtoupper((string) ($currencyCode ?: 'USD')) . number_format((float) $amount, 2);
    }

    private function orderStatusBadge(string $status): string
    {
        $classMap = [
            Order::STATUS_PENDING_PAYMENT => 'bg-warning text-dark',
            Order::STATUS_PROCESSING => 'bg-info',
            Order::STATUS_SHIPPED => 'bg-primary',
            Order::STATUS_DELIVERED => 'bg-success',
            Order::STATUS_CANCELLED => 'bg-secondary',
            Order::STATUS_RETURNED => 'bg-dark',
            Order::STATUS_FAILED => 'bg-danger',
        ];

        return '<span class="badge rounded-pill ' . ($classMap[$status] ?? 'bg-secondary') . '">'
            . e(Order::statusOptions()[$status] ?? Str::headline($status))
            . '</span>';
    }

    private function googleMapsRouteUrl(Order $order): ?string
    {
        $branchLatitude = optional($order->organization)->latitude;
        $branchLongitude = optional($order->organization)->longitude;

        if (! filled($branchLatitude) || ! filled($branchLongitude) || ! filled($order->customer_latitude) || ! filled($order->customer_longitude)) {
            return null;
        }

        return 'https://www.google.com/maps/dir/?api=1&origin='
            . urlencode($branchLatitude . ',' . $branchLongitude)
            . '&destination='
            . urlencode($order->customer_latitude . ',' . $order->customer_longitude)
            . '&travelmode=driving';
    }

    private function branchDrivers(Order $order)
    {
        return User::query()
            ->where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['driver']))
            ->whereHas('organizations', fn ($query) => $query->where('organizations.id', $order->organization_id))
            ->orderBy('name');
    }
}
