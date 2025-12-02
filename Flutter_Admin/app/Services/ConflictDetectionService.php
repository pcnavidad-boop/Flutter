<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class ConflictDetectionService
{
    /**
     * Check whether the room has an overlapping booking
     *
     * @param \App\Models\Room $room
     * @param string $start  Date string (Y-m-d)
     * @param string $end    Date string (Y-m-d)
     * @param int|null $ignoreId
     * @return bool
     */
    public function roomHasConflict($room, string $start, string $end, $ignoreId = null): bool
    {
        $q = RoomBooking::where('room_id', $room->id)
            ->where('booking_status', '!=', 'cancelled')
            // overlap test: existing.start < new.end AND existing.end > new.start
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start);

        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }

    /**
     * Check whether service is already at capacity for the given appointment.
     *
     * For time-based services (spa/restaurant/bar) we check overlapping times on the same date.
     * For non-time services (gym/pool) we check daily capacity.
     *
     * @param \App\Models\Service $service
     * @param array $data Must contain 'appointment_date' and may contain 'start_time'/'end_time'
     * @param int|null $ignoreId
     * @return bool true if conflict (i.e. fully booked)
     */
    public function serviceHasConflict($service, array $data, $ignoreId = null): bool
    {
        $base = ServiceBooking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('appointment_date', $data['appointment_date']);

        if ($ignoreId) {
            $base->where('id', '!=', $ignoreId);
        }

        // Time-based capacity: check overlapping intervals count
        if (in_array($service->service_type, ['spa', 'restaurant', 'bar'])) {
            // require start_time & end_time in $data for proper check
            if (!isset($data['start_time']) || !isset($data['end_time'])) {
                // missing times — treat as conflict-safe (do not allow)
                return true;
            }

            $count = (clone $base)
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->count();

            return $count >= ($service->capacity ?? 0);
        }

        // Non-time services: simple daily count vs capacity
        return ($base->count()) >= ($service->capacity ?? 0);
    }
}
