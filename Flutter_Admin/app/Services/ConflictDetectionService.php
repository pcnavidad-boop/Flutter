<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use Illuminate\Support\Facades\Log;

class ConflictDetectionService
{
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
     * Service conflict detection (IMPROVED)
     *
     * For time-based services (spa/restaurant/bar) we SUM guests in overlapping intervals.
     * For non-time services (gym/pool) we compare daily sum of guests vs capacity.
     *
     * @param $service
     * @param array $data
     * @param null|int $ignoreId
     * @return bool
     */
    public function serviceHasConflict($service, array $data, $ignoreId = null): bool
    {
        $base = ServiceBooking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('appointment_date', $data['appointment_date']);

        if ($ignoreId) {
            $base->where('id', '!=', $ignoreId);
        }

        // Time-based capacity: sum overlapping guests
        if (in_array($service->service_type, ['spa', 'restaurant', 'bar'])) {
            if (!isset($data['start_time']) || !isset($data['end_time'])) {
                // require times to properly check — deny if missing
                return true;
            }

            // fetch bookings for date
            $items = $base->get();

            $requestedStart = $data['start_time'];
            $requestedEnd   = $data['end_time'];
            $requestedGuests = (int) ($data['number_of_guests'] ?? 1);

            // compute concurrent guests by summing all overlapping bookings' number_of_guests
            $concurrent = 0;
            foreach ($items as $b) {
                if ($b->start_time < $requestedEnd && $b->end_time > $requestedStart) {
                    $concurrent += (int) $b->number_of_guests;
                }
            }

            // Add the new booking's guests
            $concurrent += $requestedGuests;

            return ($service->capacity && $concurrent > $service->capacity);
        }

        // Non-time services: daily capacity by summing guests
        $dailyGuests = (clone $base)->sum('number_of_guests');
        $requestedGuests = (int) ($data['number_of_guests'] ?? 1);

        return ($service->capacity && ($dailyGuests + $requestedGuests) > $service->capacity);
    }
}
