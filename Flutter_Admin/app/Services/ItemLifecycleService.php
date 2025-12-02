<?php

namespace App\Services;

use Carbon\Carbon;

class ItemLifecycleService
{
    /**
     * Ensure item (room or service) is bookable (not archived/under maintenance)
     *
     * @param mixed $item
     * @throws \Exception
     */
    public function assertItemBookable($item): void
    {
        if (isset($item->is_archived) && $item->is_archived) {
            throw new \Exception("This item is archived.");
        }

        if (isset($item->status) && $item->status === 'maintenance') {
            throw new \Exception("This item is under maintenance.");
        }
    }

    /**
     * Ensure item can be updated (not archived)
     *
     * @param mixed $item
     * @throws \Exception
     */
    public function assertItemUpdatable($item): void
    {
        if (isset($item->is_archived) && $item->is_archived) {
            throw new \Exception("Cannot update an archived item.");
        }
    }

    /**
     * Ensure changing the type of an item is allowed (no active bookings with different types)
     *
     * @param mixed $item
     * @param string $newType
     * @throws \Exception
     */
    public function assertTypeChangeAllowed($item, string $newType): void
    {
        $hasActive = $item->bookings()->where('booking_status', '!=', 'cancelled')->exists();

        if ($hasActive) {
            if ((isset($item->service_type) && $newType !== $item->service_type) ||
                (isset($item->room_type) && $newType !== $item->room_type)) {
                throw new \Exception("Cannot change item type while active bookings exist.");
            }
        }
    }

    /**
     * Ensure changing capacity won't invalidate existing bookings.
     *
     * @param mixed $item
     * @param int $newCapacity
     * @throws \Exception
     */
    public function assertCapacityChangeAllowed($item, int $newCapacity): void
    {
        foreach ($item->bookings()->where('booking_status', '!=', 'cancelled')->get() as $b) {
            if ($b->number_of_guests > $newCapacity) {
                throw new \Exception("Cannot reduce capacity below {$b->number_of_guests} guests from an existing booking.");
            }
        }
    }

    /**
     * Ensure changing operating hours won't break existing bookings (for services).
     *
     * @param mixed $service
     * @param string $newStart  'H:i'
     * @param string $newEnd    'H:i'
     * @throws \Exception
     */
    public function assertOperatingHoursChangeAllowed($service, string $newStart, string $newEnd): void
    {
        try {
            $ns = Carbon::createFromFormat('H:i', substr($newStart, 0, 5));
            $ne = Carbon::createFromFormat('H:i', substr($newEnd, 0, 5));
        } catch (\Throwable $e) {
            throw new \Exception("Invalid operating hour format.");
        }

        if ($ne->lte($ns)) {
            throw new \Exception("End time must be after start time.");
        }

        foreach ($service->bookings()->where('booking_status','!=','cancelled')->get() as $b) {
            if (!$b->start_time || !$b->end_time) {
                // if booking has no times, skip time-check (or treat as conflict depending on your policy)
                continue;
            }

            $bs = Carbon::createFromFormat('H:i', substr($b->start_time, 0, 5));
            $be = Carbon::createFromFormat('H:i', substr($b->end_time, 0, 5));

            if ($bs->lt($ns) || $be->gt($ne)) {
                throw new \Exception("Existing booking {$bs->format('H:i')}–{$be->format('H:i')} falls outside new operating hours.");
            }
        }
    }

    /**
     * Ensure item may be archived (no future non-cancelled bookings).
     *
     * @param mixed $item
     * @throws \Exception
     */
    public function assertCanArchive($item): void
    {
        $today = today()->toDateString();

        // If this is a ROOM -----------------------------------------
        if (isset($item->room_type)) {

            $hasFutureBooking = $item->bookings()
                ->where('booking_status', '!=', 'cancelled')
                ->whereDate('start_date', '>=', $today)
                ->exists();

            if ($hasFutureBooking) {
                throw new \Exception("Cannot archive this room because future bookings exist.");
            }

            return;
        }

        // If this is a SERVICE ---------------------------------------
        if (isset($item->service_type)) {

            $hasFutureBooking = $item->bookings()
                ->where('booking_status', '!=', 'cancelled')
                ->whereDate('appointment_date', '>=', $today)
                ->exists();

            if ($hasFutureBooking) {
                throw new \Exception("Cannot archive this service because future appointments exist.");
            }

            return;
        }

        // Unknown item type
        throw new \Exception("Unknown item type — cannot check archive rules.");
    }
}
