<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Organization;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\User;
use App\Services\DriverAutoDispatchService;
use App\Services\OrderPricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FrontendStaffPortalController extends Controller
{
    public function dashboard(Request $request): View|JsonResponse
    {
        $user = $this->portalUser();
        $assignedOrganizationIds = $this->assignedOrganizationIds($user);

        if ($request->ajax()) {
            $query = Order::with(['organization.location', 'customer', 'items', 'driver'])
                ->select('orders.*')
                ->orderByDesc('orders.created_at')
                ->orderByDesc('orders.id');
            $this->applyOrderVisibilityScope($query, $user, $assignedOrganizationIds);

            $statusFilter = (string) $request->input('status_filter', '');
            if ($statusFilter !== '' && $statusFilter !== 'all' && array_key_exists($statusFilter, Order::statusOptions())) {
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
                ->addColumn('status_selector', function (Order $order) {
                    return $this->orderStatusBadge($order->order_status);
                })
                ->addColumn('grand_total_display', function (Order $order) {
                    $html = '<div class="fw-semibold text-dark">' . e($this->money($order->payment_currency, $order->grand_total)) . '</div>'
                        . '<div class="small text-muted">' . e(__('subtotal')) . ': ' . e($this->money($order->payment_currency, $order->subtotal)) . '</div>';

                    if ((float) $order->discount_amount > 0) {
                        $html .= '<div class="small text-success">' . e(__('coupon_discount')) . ': -' . e($this->money($order->payment_currency, $order->discount_amount)) . '</div>';
                        if (filled($order->coupon_code)) {
                            $html .= '<div class="small text-muted">' . e(__('coupon_code')) . ': ' . e($order->coupon_code) . '</div>';
                        }
                    }

                    return $html;
                })
                ->addColumn('extra_paid_display', function (Order $order) {
                    $extraPaidAmount = (float) ($order->extra_amount_paid ?? 0);
                    $isPaid = $extraPaidAmount > 0;

                    $html = '<div class="fw-semibold text-dark">' . e($extraPaidAmount > 0 ? $this->money($order->payment_currency, $extraPaidAmount) : $this->money($order->payment_currency, 0)) . '</div>';
                    $html .= '<div class="small ' . ($isPaid ? 'text-success' : 'text-muted') . '">'
                        . e($isPaid ? __('paid') : __('not_paid_yet'))
                        . '</div>';

                    return $html;
                })
                ->addColumn('action', function (Order $order) {
                    $user = $this->portalUser();
                    $isDriver = $user->hasRole('Driver');

                    $statusButton = '<button type="button" class="frontend-action-btn frontend-action-btn--success js-open-order-status-modal" '
                        . 'data-url="' . e(route('frontend.staff.orders.update-status', $order)) . '" '
                        . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                        . 'data-current-status="' . e($order->order_status) . '" '
                        . 'data-payment-method="' . e($order->payment_method ?: '') . '" '
                        . 'data-payment-status="' . e($order->payment_status ?: '') . '" '
                        . 'title="' . __('update_status') . '"><i class="fa fa-arrows-rotate"></i></button>';
                    $markPaidButton = (!$isDriver && $order->canBeMarkedPaidManually())
                        ? '<button type="button" class="frontend-action-btn frontend-action-btn--success js-mark-order-paid" '
                            . 'data-url="' . e(route('frontend.staff.orders.mark-paid', $order)) . '" '
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'data-payment-method-label="' . e($this->paymentMethodTitle($order->payment_method)) . '" '
                            . 'title="' . __('mark_as_paid') . '"><i class="fa fa-money-check-dollar"></i></button>'
                        : '';

                    $editOrder = $isDriver
                        ? ''
                        : '<a href="' . route('frontend.staff.orders.edit', $order) . '" class="frontend-action-btn frontend-action-btn--dark" title="' . __('edit_order') . '"><i class="fa fa-pen-to-square"></i></a>';
                    $invoice = $isDriver
                        ? ''
                        : '<button type="button" class="frontend-action-btn frontend-action-btn--info js-open-order-invoice" data-url="' . e(route('frontend.staff.orders.invoice', $order)) . '" data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" title="' . __('view_invoice') . '"><i class="fa fa-file-invoice"></i></button>';
                    $pdf = $isDriver
                        ? ''
                        : '<a href="' . route('frontend.staff.orders.invoice.download', $order) . '" class="frontend-action-btn frontend-action-btn--primary" title="' . __('download_pdf') . '"><i class="fa fa-file-pdf"></i></a>';

                    $canShowRoute = filled($order->customer_latitude)
                        && filled($order->customer_longitude)
                        && filled(optional($order->organization)->latitude)
                        && filled(optional($order->organization)->longitude);

                    $routeButton = '<button type="button" class="frontend-action-btn frontend-action-btn--warning js-show-order-route" '
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
                        . 'title="' . __('view_route') . '"><i class="fa fa-route"></i></button>';

                    $assignDriverButton = $isDriver
                        ? ''
                        : '<button type="button" class="frontend-action-btn frontend-action-btn--primary js-open-driver-assign-modal" '
                            . 'data-order-id="' . e((string) $order->id) . '" '
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'data-driver-name="' . e(optional($order->driver)->name ?: '') . '" '
                            . 'title="' . __('assign_driver') . '"><i class="fa fa-user-check"></i></button>';
                    $changeBranchButton = $isDriver
                        ? ''
                        : '<button type="button" class="frontend-action-btn frontend-action-btn--dark js-open-branch-change-modal" '
                            . 'data-order-id="' . e((string) $order->id) . '" '
                            . 'data-order-number="' . e($order->order_number ?: ('#' . $order->id)) . '" '
                            . 'title="' . __('change_branch') . '"><i class="fa fa-code-branch"></i></button>';

                    $deleteButton = $isDriver
                        ? ''
                        : '<!-- <form action="' . route('frontend.staff.orders.destroy', $order) . '" method="POST" style="display:inline;" class="js-confirm-delete">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="frontend-action-btn frontend-action-btn--danger" title="' . __('delete') . '"><i class="fa fa-trash"></i></button></form> -->';

                    return '<div class="frontend-action-row">' . $statusButton . $markPaidButton . $editOrder . $invoice . $pdf . $routeButton . $assignDriverButton . $changeBranchButton . $deleteButton . '</div>';
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
                    'extra_paid_display',
                    'action',
                ])
                ->make(true);
        }

        return view('frontend.staff.dashboard', [
            'portalUser' => $user,
            'portalRoleLabel' => $this->portalRoleLabel($user),
            'statusCards' => $this->statusCards($assignedOrganizationIds),
            'statusOptions' => Order::statusOptions(),
            'mapProvider' => Setting::get('map_provider', 'leaflet'),
            'googleMapsApiKey' => Setting::get('google_maps_api_key', ''),
            'portalUnreadNotificationCount' => AdminNotification::query()
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'portalLatestNotificationId' => (int) (AdminNotification::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->value('id') ?? 0),
        ]);
    }

    public function notificationSummary(): JsonResponse
    {
        $user = $this->portalUser();
        $latestNotification = AdminNotification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        return response()->json([
            'unread_count' => AdminNotification::query()
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'latest_id' => (int) ($latestNotification?->id ?? 0),
            'latest_title' => $latestNotification?->title,
            'latest_message' => $latestNotification?->message,
            'latest_type' => $latestNotification?->type,
            'latest_order_id' => $latestNotification?->order_id,
        ]);
    }

    public function routePlannerData(Request $request): JsonResponse
    {
        $user = $this->portalUser();
        $assignedOrganizationIds = $this->assignedOrganizationIds($user);

        $query = Order::with(['organization.location'])
            ->select('orders.*');

        $this->applyOrderVisibilityScope($query, $user, $assignedOrganizationIds);

        $statusFilter = (string) $request->input('status_filter', '');
        if ($statusFilter !== '' && $statusFilter !== 'all' && array_key_exists($statusFilter, Order::statusOptions())) {
            $query->where('orders.order_status', $statusFilter);
        }

        $orders = $query->get()
            ->filter(function (Order $order) {
                return filled($order->customer_latitude)
                    && filled($order->customer_longitude)
                    && filled(optional($order->organization)->latitude)
                    && filled(optional($order->organization)->longitude);
            })
            ->values()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?: ('#' . $order->id),
                    'status' => $order->order_status,
                    'customer_name' => trim($order->first_name . ' ' . $order->last_name),
                    'customer_address' => trim(implode(', ', array_filter([$order->address, $order->city, $order->country]))),
                    'customer_latitude' => (float) $order->customer_latitude,
                    'customer_longitude' => (float) $order->customer_longitude,
                    'branch_name' => optional($order->organization)->name ?: '-',
                    'branch_address' => optional($order->organization)->address ?: '-',
                    'branch_latitude' => (float) optional($order->organization)->latitude,
                    'branch_longitude' => (float) optional($order->organization)->longitude,
                    'distance_km' => (float) ($order->delivery_distance_km ?: 0),
                ];
            })
            ->all();

        return response()->json([
            'orders' => $orders,
            'status_filter' => $statusFilter ?: 'all',
        ]);
    }

    public function availableDrivers(Order $order): JsonResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        $drivers = User::query()
            ->where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['driver']))
            ->whereHas('organizations', fn ($query) => $query->where('organizations.id', $order->organization_id))
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
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

    public function assignDriver(Request $request, Order $order, DriverAutoDispatchService $driverAutoDispatchService): JsonResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $driver = User::query()
            ->where('id', $validated['driver_id'])
            ->where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['driver']))
            ->whereHas('organizations', fn ($query) => $query->where('organizations.id', $order->organization_id))
            ->first();

        if (! $driver) {
            return response()->json([
                'message' => 'Selected driver is not valid for this branch.',
            ], 422);
        }

        $driverAutoDispatchService->stopForManualAssignment($order, $driver->id);

        $this->notifyAssignedDriver($order, $driver);

        return response()->json([
            'message' => 'Driver assigned successfully.',
            'driver_name' => $driver->name,
        ]);
    }

    public function availableBranches(Order $order): JsonResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

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

    public function updateBranch(Request $request, Order $order, DriverAutoDispatchService $driverAutoDispatchService): JsonResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

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
        $order->save();
        if ($currentBranchId !== $nextBranchId) {
            $driverAutoDispatchService->resetForBranchChange($order);
        }

        return response()->json([
            'message' => __('order_branch_updated_successfully'),
            'branch_name' => $branch->name,
            'driver_cleared' => $currentBranchId !== $nextBranchId,
        ]);
    }

    public function edit(Order $order): View
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        $order->load(['items', 'customer']);
        $order->items->each(function ($item) {
            $item->addons = is_string($item->addons) ? json_decode($item->addons, true) : $item->addons;
        });

        return view('frontend.staff.edit-order', [
            'portalUser' => $user,
            'portalRoleLabel' => $this->portalRoleLabel($user),
            'order' => $order,
            'products' => Product::with(['addons.values'])->where('status', true)->get(),
            'activeTax' => Tax::where('status', true)->orderBy('id')->first(),
        ]);
    }

    public function update(Request $request, Order $order, OrderPricingService $pricingService): RedirectResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

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

            if (! empty($existingAddonsData)) {
                foreach ($existingAddonsData as $entry) {
                    $item = OrderItem::where('order_id', $order->id)->where('id', $entry['id'] ?? null)->first();
                    if (! $item) {
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

            if (! empty($deletedIds)) {
                $order->items()->whereIn('id', $deletedIds)->delete();
            }

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

            $updatedItems = $order->items()->get()->toArray();
            $totals = $pricingService->calculate(
                $updatedItems,
                $order->order_type,
                $order->customer
            );

            $discountAmount = min((float) $order->discount_amount, max((float) ($totals['subtotal'] + $existingAddonTotal), 0));

            $order->update([
                'subtotal' => $totals['subtotal'] + $existingAddonTotal,
                'shipping_costs' => $totals['shipping_costs'],
                'vat_amount' => $totals['vat_amount'] + $existingAddonTaxTotal,
                'discount_amount' => $discountAmount,
                'grand_total' => ($totals['grand_total'] + $existingAddonTotal + $existingAddonTaxTotal) - $discountAmount,
                'admin_updated' => true,
            ]);

            $message = __('order_updated_successfully');
            if ($order->grand_total > $order->amount_paid) {
                \App\Services\OrderEmailService::sendStatusEmail($order, 'order_balance_payment');
                $message .= ' ' . __('payment_link_sent_to_customer');
            }

            return redirect()->route('frontend.staff.dashboard')->with('success', $message);
        });
    }

    public function destroy(Order $order): RedirectResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return redirect()->route('frontend.staff.dashboard')->with('success', __('order_deleted_successfully'));
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);

        $validated = $request->validate([
            'order_status' => ['required', Rule::in(array_keys(Order::statusOptions()))],
            'confirm_payment_received' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'order_status' => $validated['order_status'],
        ];

        if (
            $validated['order_status'] === Order::STATUS_DELIVERED
            && $order->requiresOfflinePaymentConfirmationForDelivery()
            && $request->boolean('confirm_payment_received')
        ) {
            $payload['payment_status'] = 'paid';
            $payload['amount_paid'] = (float) $order->grand_total;
        }

        $order->update($payload);

        return response()->json([
            'message' => __('order_status_updated_successfully'),
            'status_badge' => $this->orderStatusBadge($order->order_status),
        ]);
    }

    public function markPaid(Order $order): JsonResponse
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        if (! $order->canBeMarkedPaidManually()) {
            return response()->json([
                'message' => __('order_is_already_marked_paid'),
            ], 422);
        }

        $order->update([
            'payment_status' => 'paid',
            'amount_paid' => (float) $order->grand_total,
        ]);

        return response()->json([
            'message' => __('order_marked_paid_successfully'),
        ]);
    }

    public function acceptDriverOffer(Order $order, DriverAutoDispatchService $driverAutoDispatchService): JsonResponse
    {
        $user = $this->portalUser();
        if (! $user->hasRole('Driver')) {
            abort(403);
        }

        if (! $driverAutoDispatchService->driverCanRespond($order->fresh(), $user)) {
            return response()->json([
                'message' => __('driver_offer_not_available'),
            ], 422);
        }

        $driverAutoDispatchService->accept($order->fresh(), $user);

        return response()->json([
            'message' => __('driver_offer_accepted'),
        ]);
    }

    public function rejectDriverOffer(Order $order, DriverAutoDispatchService $driverAutoDispatchService): JsonResponse
    {
        $user = $this->portalUser();
        if (! $user->hasRole('Driver')) {
            abort(403);
        }

        if (! $driverAutoDispatchService->driverCanRespond($order->fresh(), $user)) {
            return response()->json([
                'message' => __('driver_offer_not_available'),
            ], 422);
        }

        $driverAutoDispatchService->reject($order->fresh(), $user);

        return response()->json([
            'message' => __('driver_offer_rejected'),
        ]);
    }

    public function invoice(Order $order): View
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        return view('frontend.staff.invoice', array_merge($this->invoiceViewData($order), [
            'portalUser' => $user,
            'portalRoleLabel' => $this->portalRoleLabel($user),
        ]));
    }

    public function downloadInvoice(Order $order)
    {
        $user = $this->portalUser();
        $this->authorizeOrderAccess($order, $user);
        $this->ensureNotDriver($user);

        $pdf = Pdf::loadView('orders.invoice-pdf', $this->invoiceViewData($order));

        return $pdf->setPaper('a4')->download(($order->order_number ?: ('order-' . $order->id)) . '.pdf');
    }

    private function portalUser(): User
    {
        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['Staff', 'Driver'])) {
            abort(403);
        }

        return $user;
    }

    private function portalRoleLabel(User $user): string
    {
        return $user->hasRole('Driver') ? 'Driver' : 'Staff';
    }

    private function statusCards(array $assignedOrganizationIds): array
    {
        $user = $this->portalUser();
        $query = Order::query();
        $this->applyOrderVisibilityScope($query, $user, $assignedOrganizationIds);

        $statusCounts = $query
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $totalQuery = Order::query();
        $this->applyOrderVisibilityScope($totalQuery, $user, $assignedOrganizationIds);
        $totalOrders = $totalQuery->count();

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

    private function assignedOrganizationIds(User $user): array
    {
        return $user->organizations()->pluck('organizations.id')->map(fn ($id) => (int) $id)->all();
    }

    private function applyOrganizationScope($query, array $assignedOrganizationIds): void
    {
        if (empty($assignedOrganizationIds)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('organization_id', $assignedOrganizationIds);
    }

    private function applyOrderVisibilityScope($query, User $user, array $assignedOrganizationIds): void
    {
        $this->applyOrganizationScope($query, $assignedOrganizationIds);

        if ($user->hasRole('Driver')) {
            $query->where('driver_id', $user->id);
        }
    }

    private function authorizeOrderAccess(Order $order, User $user): void
    {
        $assignedOrganizationIds = $this->assignedOrganizationIds($user);
        if (empty($assignedOrganizationIds) || ! in_array((int) $order->organization_id, $assignedOrganizationIds, true)) {
            abort(403);
        }

        if ($user->hasRole('Driver') && (int) $order->driver_id !== (int) $user->id) {
            abort(403);
        }
    }

    private function ensureNotDriver(User $user): void
    {
        if ($user->hasRole('Driver')) {
            abort(403);
        }
    }

    private function notifyAssignedDriver(Order $order, User $driver): void
    {
        $orderLabel = $order->order_number ?: ('#' . $order->id);
        $branchName = optional($order->organization)->name ?: ($order->city ?: 'your branch');
        $customerName = trim($order->first_name . ' ' . $order->last_name);

        AdminNotification::create([
            'user_id' => $driver->id,
            'order_id' => $order->id,
            'type' => 'driver_assignment',
            'title' => 'New delivery assigned',
            'message' => 'Order ' . $orderLabel . ' for ' . ($customerName !== '' ? $customerName : 'a customer') . ' has been assigned to you from ' . $branchName . '.',
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}
