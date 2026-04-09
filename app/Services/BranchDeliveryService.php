<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\ShippingFee;
use Illuminate\Support\Facades\Http;

class BranchDeliveryService
{
    public function summarizeForCustomer(?Customer $customer, float $subtotal, string $orderType = 'delivery'): array
    {
        if (! $customer) {
            return $this->emptySummary($subtotal, $orderType);
        }

        [$latitude, $longitude, $geocodedAddress] = $this->resolveCustomerCoordinates($customer);

        if ($geocodedAddress && (! $customer->latitude || ! $customer->longitude || blank($customer->address))) {
            $customer->forceFill([
                'address' => $geocodedAddress['address'] ?: $customer->address,
                'city' => $geocodedAddress['city'] ?: $customer->city,
                'postal_code' => $geocodedAddress['postal_code'] ?: $customer->postal_code,
                'country' => $geocodedAddress['country'] ?: $customer->country,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ])->save();
        }

        return $this->summarizeForCoordinates($latitude, $longitude, $subtotal, $orderType);
    }

    public function summarizeForCoordinates(?float $latitude, ?float $longitude, float $subtotal, string $orderType = 'delivery'): array
    {
        $summary = $this->emptySummary($subtotal, $orderType);

        if ($latitude === null || $longitude === null) {
            return $summary;
        }

        $closestBranch = $this->closestBranch($latitude, $longitude);
        if (! $closestBranch) {
            return $summary;
        }

        $summary['customer_latitude'] = $latitude;
        $summary['customer_longitude'] = $longitude;
        $summary['has_address'] = true;
        $summary['branch'] = [
            'id' => $closestBranch['branch']->id,
            'name' => $closestBranch['branch']->name,
            'address' => $closestBranch['branch']->address,
            'location' => optional($closestBranch['branch']->location)->name,
        ];
        $summary['distance_km'] = round($closestBranch['distance_km'], 2);

        if ($orderType === 'pick_up') {
            return $summary;
        }

        $matchingFee = $this->matchingShippingFee($closestBranch['distance_km'], $subtotal, 'delivery');
        if ($matchingFee) {
            $summary['shipping_fee'] = [
                'id' => $matchingFee->id,
                'title' => $matchingFee->title,
                'fee' => (float) $matchingFee->fee,
                'min_order_amount' => (float) $matchingFee->min_order_amount,
            ];
            $summary['shipping_cost'] = round((float) $matchingFee->fee, 2);
            $summary['requires_minimum_order'] = (float) $matchingFee->min_order_amount > 0;
        }

        return $summary;
    }

    public function geocodeAddress(array $payload): ?array
    {
        $query = $this->buildAddressQuery($payload);
        if ($query === '') {
            return null;
        }

        $response = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Foodshop') . '/1.0',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 1,
                'q' => $query,
            ]);

        if (! $response->ok()) {
            return null;
        }

        $item = collect($response->json() ?: [])->first();
        if (! is_array($item)) {
            return null;
        }

        return $this->normalizeGeocodeResult($item);
    }

    public function reverseGeocodeCoordinates(float $latitude, float $longitude): ?array
    {
        $response = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Foodshop') . '/1.0',
            ])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'lat' => $latitude,
                'lon' => $longitude,
            ]);

        if (! $response->ok()) {
            return null;
        }

        $item = $response->json();
        if (! is_array($item)) {
            return null;
        }

        return $this->normalizeGeocodeResult([
            'display_name' => $item['display_name'] ?? '',
            'lat' => $item['lat'] ?? $latitude,
            'lon' => $item['lon'] ?? $longitude,
            'address' => $item['address'] ?? [],
            'name' => $item['name'] ?? '',
        ]);
    }

    public function resolveCustomerCoordinates(Customer $customer): array
    {
        if ($customer->latitude !== null && $customer->longitude !== null) {
            return [(float) $customer->latitude, (float) $customer->longitude, null];
        }

        $geocoded = $this->geocodeAddress([
            'address' => $customer->address,
            'city' => $customer->city,
            'postal_code' => $customer->postal_code,
            'country' => $customer->country,
        ]);

        if (! $geocoded) {
            return [null, null, null];
        }

        return [(float) $geocoded['latitude'], (float) $geocoded['longitude'], $geocoded];
    }

    public function closestBranch(float $latitude, float $longitude): ?array
    {
        $branches = Organization::with('location')
            ->where('status', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $closest = null;

        foreach ($branches as $branch) {
            $distance = $this->distanceInKm($latitude, $longitude, (float) $branch->latitude, (float) $branch->longitude);
            if (! $closest || $distance < $closest['distance_km']) {
                $closest = [
                    'branch' => $branch,
                    'distance_km' => $distance,
                ];
            }
        }

        return $closest;
    }

    public function matchingShippingFee(float $distanceKm, float $subtotal, string $deliveryType = 'delivery'): ?ShippingFee
    {
        return ShippingFee::query()
            ->where('status', true)
            ->where('delivery_type', $deliveryType)
            ->orderBy('maximum_order_amount')
            ->orderBy('fee')
            ->get()
            ->first(function (ShippingFee $shippingFee) use ($distanceKm, $subtotal) {
                $maximumOrderAmount = (float) $shippingFee->maximum_order_amount;
                if ($maximumOrderAmount > 0 && $subtotal > $maximumOrderAmount) {
                    return false;
                }

                return $this->matchesDistanceCondition($distanceKm, (string) $shippingFee->condition_type, (string) $shippingFee->distance_value);
            });
    }

    private function emptySummary(float $subtotal, string $orderType): array
    {
        return [
            'subtotal' => round($subtotal, 2),
            'order_type' => $orderType,
            'has_address' => false,
            'customer_latitude' => null,
            'customer_longitude' => null,
            'distance_km' => null,
            'shipping_cost' => 0.0,
            'shipping_fee' => null,
            'requires_minimum_order' => false,
            'branch' => null,
        ];
    }

    private function buildAddressQuery(array $payload): string
    {
        return collect([
            trim((string) ($payload['address'] ?? '')),
            trim((string) ($payload['city'] ?? '')),
            trim((string) ($payload['postal_code'] ?? '')),
            trim((string) ($payload['country'] ?? '')),
        ])->filter(fn ($value) => $value !== '')->implode(', ');
    }

    private function normalizeGeocodeResult(array $item): array
    {
        $addressParts = is_array($item['address'] ?? null) ? $item['address'] : [];

        return [
            'address' => (string) ($item['display_name'] ?? ''),
            'latitude' => (float) ($item['lat'] ?? 0),
            'longitude' => (float) ($item['lon'] ?? 0),
            'city' => (string) ($addressParts['city'] ?? $addressParts['town'] ?? $addressParts['village'] ?? $addressParts['municipality'] ?? ''),
            'postal_code' => (string) ($addressParts['postcode'] ?? ''),
            'country' => (string) ($addressParts['country'] ?? ''),
            'country_code' => strtoupper((string) ($addressParts['country_code'] ?? '')),
        ];
    }

    private function matchesDistanceCondition(float $distanceKm, string $conditionType, string $distanceValue): bool
    {
        $distanceValue = trim($distanceValue);

        return match ($conditionType) {
            'less_than' => $distanceKm < (float) $distanceValue,
            'equal_to' => abs($distanceKm - (float) $distanceValue) < 0.01,
            'greater_than' => $distanceKm > (float) $distanceValue,
            'between' => $this->distanceBetween($distanceKm, $distanceValue),
            default => false,
        };
    }

    private function distanceBetween(float $distanceKm, string $distanceValue): bool
    {
        [$min, $max] = array_pad(array_map('trim', explode('-', $distanceValue, 2)), 2, null);
        if ($min === null || $max === null || $min === '' || $max === '') {
            return false;
        }

        return $distanceKm >= (float) $min && $distanceKm <= (float) $max;
    }

    private function distanceInKm(float $latitudeOne, float $longitudeOne, float $latitudeTwo, float $longitudeTwo): float
    {
        $earthRadius = 6371;
        $deltaLatitude = deg2rad($latitudeTwo - $latitudeOne);
        $deltaLongitude = deg2rad($longitudeTwo - $longitudeOne);

        $a = sin($deltaLatitude / 2) ** 2
            + cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) * sin($deltaLongitude / 2) ** 2;

        return $earthRadius * (2 * asin(min(1, sqrt($a))));
    }
}
