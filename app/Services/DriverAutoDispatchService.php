<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class DriverAutoDispatchService
{
    public function dispatchPendingOffers(): int
    {
        $orders = Order::query()
            ->whereNull('driver_id')
            ->whereNotIn('order_status', [
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
                Order::STATUS_RETURNED,
                Order::STATUS_FAILED,
            ])
            ->whereNotNull('organization_id')
            ->where(function ($query) {
                $query->whereNull('driver_offer_status')
                    ->orWhereIn('driver_offer_status', [
                        Order::DRIVER_OFFER_PENDING,
                        Order::DRIVER_OFFER_REJECTED,
                    ]);
            })
            ->orderBy('id')
            ->get();

        $offeredCount = 0;

        foreach ($orders as $order) {
            if ($this->offerNextDriver($order)) {
                $offeredCount++;
            }
        }

        return $offeredCount;
    }

    public function offerNextDriver(Order $order): bool
    {
        if (! $order->canAutoOfferToDrivers()) {
            return false;
        }

        $drivers = $this->branchDrivers($order);
        if ($drivers->isEmpty()) {
            $order->forceFill([
                'driver_offer_status' => Order::DRIVER_OFFER_STOPPED,
                'driver_offer_driver_id' => null,
            ])->save();

            return false;
        }

        $attempts = max((int) ($order->driver_offer_attempts ?? 0), 0);
        $nextIndex = $attempts % $drivers->count();
        /** @var User $driver */
        $driver = $drivers->values()->get($nextIndex);

        $order->forceFill([
            'driver_offer_status' => Order::DRIVER_OFFER_OFFERED,
            'driver_offer_driver_id' => $driver->id,
            'driver_offer_attempts' => $attempts + 1,
            'driver_offer_sent_at' => now(),
            'driver_offer_responded_at' => null,
        ])->save();

        $this->notifyDriverOffer($order, $driver);

        return true;
    }

    public function stopForManualAssignment(Order $order, ?int $driverId = null): void
    {
        $order->forceFill([
            'driver_id' => $driverId ?? $order->driver_id,
            'driver_offer_status' => Order::DRIVER_OFFER_STOPPED,
            'driver_offer_driver_id' => null,
            'driver_offer_sent_at' => null,
            'driver_offer_responded_at' => now(),
        ])->save();
    }

    public function resetForBranchChange(Order $order): void
    {
        $order->forceFill([
            'driver_id' => null,
            'driver_offer_status' => Order::DRIVER_OFFER_PENDING,
            'driver_offer_driver_id' => null,
            'driver_offer_attempts' => 0,
            'driver_offer_sent_at' => null,
            'driver_offer_responded_at' => null,
        ])->save();
    }

    public function accept(Order $order, User $driver): void
    {
        $order->forceFill([
            'driver_id' => $driver->id,
            'driver_offer_status' => Order::DRIVER_OFFER_ACCEPTED,
            'driver_offer_driver_id' => $driver->id,
            'driver_offer_responded_at' => now(),
        ])->save();

        AdminNotification::query()
            ->where('user_id', $driver->id)
            ->where('order_id', $order->id)
            ->where('type', 'driver_offer')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function reject(Order $order, User $driver): void
    {
        $order->forceFill([
            'driver_offer_status' => Order::DRIVER_OFFER_REJECTED,
            'driver_offer_driver_id' => null,
            'driver_offer_responded_at' => now(),
        ])->save();

        AdminNotification::query()
            ->where('user_id', $driver->id)
            ->where('order_id', $order->id)
            ->where('type', 'driver_offer')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function driverCanRespond(Order $order, User $driver): bool
    {
        return (int) $order->driver_id === 0
            && $order->driver_offer_status === Order::DRIVER_OFFER_OFFERED
            && (int) $order->driver_offer_driver_id === (int) $driver->id
            && ! $order->isFinalizedForDriverDispatch();
    }

    private function notifyDriverOffer(Order $order, User $driver): void
    {
        $orderLabel = $order->order_number ?: ('#' . $order->id);
        $branchName = optional($order->organization)->name ?: ($order->city ?: 'your branch');
        $customerName = trim($order->first_name . ' ' . $order->last_name);

        AdminNotification::create([
            'user_id' => $driver->id,
            'order_id' => $order->id,
            'type' => 'driver_offer',
            'title' => 'New order available',
            'message' => 'Order ' . $orderLabel . ' for ' . ($customerName !== '' ? $customerName : 'a customer') . ' is available from ' . $branchName . '. Do you want to take this order?',
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    private function branchDrivers(Order $order): Collection
    {
        return User::query()
            ->where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['driver']))
            ->whereHas('organizations', fn ($query) => $query->where('organizations.id', $order->organization_id))
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }
}
