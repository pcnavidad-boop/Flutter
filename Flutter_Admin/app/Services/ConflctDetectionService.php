<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class ConflictDetectionService
{
    /**
     * Room conflict check.
     *
     * $start and $end should be ISO dates (Y-m-d).
     */
    public function roomHasConflict($room, string $start, string $end, $ignoreId = null): bool
    {
        $q = RoomBooking::where('room_id', $room->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start);

        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }

    /**
     * Service booking time conflict.
     *
     * $data must include: appointment_date, start_time, end_time
     */
    public function serviceHasConflict($service, array $data, $ignoreId = null): bool
    {
        $base = ServiceBooking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('appointment_date', $data['appointment_date']);

        if ($ignoreId) {
            $base->where('id', '!=', $ignoreId);
        }

        // Time-overlapping capacity services (spa, restaurant, bar)
        if (in_array($service->service_type, ['spa', 'restaurant', 'bar'])) {
            $count = (clone $base)
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->count();

            return $count >= $service->capacity;
        }

        // Date-only capacity services (gym, swimming_pool)
        return $base->count() >= $service->capacity;
    }
}
