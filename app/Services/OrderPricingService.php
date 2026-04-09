<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tax;

class OrderPricingService
{
    private BranchDeliveryService $branchDeliveryService;

    public function __construct(BranchDeliveryService $branchDeliveryService)
    {
        $this->branchDeliveryService = $branchDeliveryService;
    }

    /**
     * Calculate all totals for an order.
     * 
     * @param array $items Array of items (with line_total)
     * @param string $orderType 'delivery' or 'pick_up'
     * @param Customer|null $customer
     * @return array [subtotal, shipping_cost, vat_amount, grand_total, delivery_summary]
     */
    public function calculate(array $items, string $orderType, ?Customer $customer = null): array
    {
        $subtotal = collect($items)->sum(fn ($item) => (float) ($item['line_total'] ?? 0));
        
        $deliverySummary = $this->branchDeliveryService->summarizeForCustomer($customer, $subtotal, $orderType);
        $shippingCosts = (float) ($deliverySummary['shipping_cost'] ?? 0);

        $tax = Tax::where('status', true)->orderBy('id')->first();
        $vatAmount = 0;
        if ($tax) {
            $vatAmount = $tax->calculation_type === 'percentage'
                ? $subtotal * ((float) $tax->amount / 100)
                : (float) $tax->amount;
        }

        $grandTotal = $subtotal + $shippingCosts + $vatAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'shipping_costs' => round($shippingCosts, 2),
            'vat_amount' => round($vatAmount, 2),
            'grand_total' => round($grandTotal, 2),
            'delivery_summary' => $deliverySummary,
        ];
    }
}
