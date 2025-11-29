<?php

namespace App\Services;

class ServiceRulesService
{
    public function determinePriceType(string $serviceType): string
    {
        $map = [
            'restaurant' => 'per_person',
            'bar' => 'per_person',
            'spa' => 'per_hour',
            'gym' => 'per_day',
            'swimming_pool' => 'per_day',
        ];

        if (!isset($map[$serviceType])) {
            throw new \Exception("Unknown service type '{$serviceType}'.");
        }

        return $map[$serviceType];
    }

    public function validateCapacity(string $serviceType, ?int $capacity): int
    {
        if (in_array($serviceType, ['restaurant','bar','spa'])) {
            if (!$capacity || $capacity < 1) {
                throw new \Exception("Capacity is required for {$serviceType}.");
            }
            return $capacity;
        }

        // Reasonable default for gym/pool
        return $capacity ?: 20;
    }

    public function validateOperatingHours(string $start, string $end): void
    {
        if ($end <= $start) {
            throw new \Exception("End time must be after start time.");
        }
    }
}
