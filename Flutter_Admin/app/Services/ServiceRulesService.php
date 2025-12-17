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
            'restaurant'    => [1, 100],
            'bar'           => [1, 75],
            'spa'           => [1, 25],
            'gym'           => [1, 25],
            'swimming_pool' => [1, 50],
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
     *
     * Applies RULESET A:
     * - restaurant, bar, spa → strict hourly (:00 minutes only)
     * - gym & swimming_pool → allow minute granularity
     */
    public function validateOperatingHours(string $start, string $end, string $serviceType = null): void
    {
        try {
            $s = Carbon::parse($start);
            $e = Carbon::parse($end);
        } catch (\Throwable $e) {
            throw new \Exception("Invalid time format. Please use a valid time.");
        }

        // 1. End time must be after start time
        if ($e->lte($s)) {
            throw new \Exception("End time must be after start time.");
        }

        // 2. RULESET A — Strict hourly types must have :00 minutes
        $strictHourlyTypes = ['restaurant', 'bar', 'spa'];

        if ($serviceType && in_array($serviceType, $strictHourlyTypes)) {

            if ($s->minute !== 0 || $e->minute !== 0) {
                throw new \Exception("{$this->label($serviceType)} requires exact hourly slots (minutes must be :00).");
            }

            $minutesDiff = $s->diffInMinutes($e);
            
            if ($minutesDiff < 60) {
                throw new \Exception("Operating hours must be at least 1 hour for {$serviceType} services.");
            }
        }
    }

    /**
     * Validate booking inside operating hours.
     */
    public function assertBookingWithinHours($service, $start, $end)
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);

        $svcStart = Carbon::parse($service->start_time);
        $svcEnd   = Carbon::parse($service->end_time);

        if ($s->lt($svcStart) || $e->gt($svcEnd)) {
            throw new \Exception(
                "Booking time must be within service operating hours ({$svcStart->format('H:i')} - {$svcEnd->format('H:i')})."
            );
        }
    }


    /**
     * Human-readable labels for error messages.
     */
    private function label(string $type): string
    {
        return ucfirst(str_replace('_', ' ', $type));
    }
}
