<?php

namespace App\Services;

use Carbon\Carbon;

class ServiceRulesService
{
    /**
     * Validate capacity based on service type.
     */
    public function validateCapacity(string $serviceType, ?int $capacity): int
    {
        $rules = [
            'restaurant'    => [1, 500],
            'bar'           => [1, 500],
            'spa'           => [1, 40],
            'gym'           => [1, 500],
            'swimming_pool' => [1, 500],
        ];

        if (!isset($rules[$serviceType])) {
            throw new \Exception("Unknown service type '{$serviceType}'.");
        }

        if (!$capacity) {
            throw new \Exception("Capacity is required for {$serviceType}.");
        }

        [$min, $max] = $rules[$serviceType];

        if ($capacity < $min || $capacity > $max) {
            throw new \Exception("Capacity for {$serviceType} must be between {$min} and {$max}.");
        }

        return $capacity;
    }

    /**
     * Validate operating hours.
     */
    public function validateOperatingHours(string $start, string $end): void
    {
        try {
            $s = Carbon::createFromFormat('H:i', $start);
            $e = Carbon::createFromFormat('H:i', $end);
        } catch (\Throwable $e) {
            throw new \Exception("Invalid time format. Use H:i.");
        }

        if ($e->lte($s)) {
            throw new \Exception("End time must be after start time.");
        }
    }

    /**
     * Validate booking inside operating hours.
     */
    public function assertBookingWithinHours($service, $start, $end)
    {
        $svcStart = substr($service->start_time, 0, 5);
        $svcEnd   = substr($service->end_time, 0, 5);

        if ($start < $svcStart || $end > $svcEnd) {
            throw new \Exception("Booking time must be within service operating hours ({$svcStart} - {$svcEnd}).");
        }
    }
}
