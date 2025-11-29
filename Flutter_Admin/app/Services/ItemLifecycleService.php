<?php

namespace App\Services;

use Carbon\Carbon;

class ItemLifecycleService
{
    /**
     * Ensure item (room or service) can be booked.
     */
    public function assertItemBookable($item): void
    {
        if (property_exists($item, 'is_archived') && $item->is_archived) {
            throw new \Exception("This item is archived.");
        }

        if (property_exists($item, 'status') && $item->status === 'maintenance') {
            throw new \Exception("This item is under maintenance.");
        }
    }

    /**
     * Ensure item can be updated (not archived).
     */
    public function assertItemUpdatable($item): void
    {
        if (property_exists($item, 'is_archived') && $item->is_archived) {
            throw new \Exception("Cannot update an archived item.");
        }
    }

    /**
     * Prevent type change if active bookings exist.
     */
    public function assertTypeChangeAllowed($item, string $newType): void
    {
        // Detect model type by class name to check proper column
        $hasActive = $item->bookings()->where('booking_status', '!=', 'cancelled')->exists();

        if ($hasActive) {
            // for services: compare service_type; for rooms: compare room_type
            if ((property_exists($item, 'service_type') && $newType !== $item->service_type)
                || (property_exists($item, 'room_type') && $newType !== $item->room_type)) {
                throw new \Exception("Cannot change item type while active bookings exist.");
            }
        }
    }

    /**
     * Prevent capacity reduction that would break existing bookings.
     */
    public function assertCapacityChangeAllowed($item, int $newCapacity): void
    {
        foreach ($item->bookings()->where('booking_status', '!=', 'cancelled')->get() as $b) {
            if ($b->number_of_guests > $newCapacity) {
                throw new \Exception("Cannot reduce capacity below existing booking of {$b->number_of_guests} guests.");
            }
        }
    }

    /**
     * Prevent invalid operating hour changes (services only).
     */
    public function assertOperatingHoursChangeAllowed($service, string $newStart, string $newEnd): void
    {
        $ns = Carbon::parse($newStart);
        $ne = Carbon::parse($newEnd);

        foreach ($service->bookings()->where('booking_status', '!=', 'cancelled')->get() as $b) {
            $bs = Carbon::parse($b->start_time);
            $be = Carbon::parse($b->end_time);

            if ($bs->lt($ns) || $be->gt($ne)) {
                throw new \Exception("Cannot change hours: booking {$bs->format('H:i')}–{$be->format('H:i')} is outside new schedule.");
            }
        }
    }

    /**
     * Prevent archiving items with future bookings.
     */
    public function assertCanArchive($item): void
    {
        // Distinguish between room-type bookings and service-type bookings.
        $hasFutureRoomBooking = false;
        $hasFutureServiceBooking = false;

        // Room bookings: start_date
        if (property_exists($item, 'room_type') || property_exists($item, 'capacity') && method_exists($item, 'bookings')) {
            $hasFutureRoomBooking = $item->bookings()
                ->where('booking_status', '!=', 'cancelled')
                ->whereDate('start_date', '>=', today())
                ->exists();
        }

        // Service bookings: appointment_date
        $hasFutureServiceBooking = $item->bookings()
            ->where('booking_status', '!=', 'cancelled')
            ->where(function ($q) {
                // use appointment_date if present
                $q->whereDate('appointment_date', '>=', today());
            })
            ->exists();

        if ($hasFutureRoomBooking || $hasFutureServiceBooking) {
            throw new \Exception("Cannot archive this item because future bookings exist.");
        }
    }
}
