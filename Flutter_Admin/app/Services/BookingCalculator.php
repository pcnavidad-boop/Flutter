<?php

namespace App\Services;

use Carbon\Carbon;

class BookingCalculator
{
    /**
     * Return the computed total price for a booking.
     * Accepts either RoomBooking or ServiceBooking model instance.
     */
    public static function computeTotal($booking): float
    {
        // If booking already has total_price set, respect it.
        if (!empty($booking->total_price)) {
            return (float) $booking->total_price;
        }

        // Room booking calculation
        if (method_exists($booking, 'room') && $booking->room) {
            $room = $booking->room;

            // If check-in & check-out present, compute nights
            if ($booking->check_in_date && $booking->check_out_date) {
                $start = Carbon::parse($booking->check_in_date);
                $end   = Carbon::parse($booking->check_out_date);
                $nights = max(1, $end->diffInDays($start));
                return round($nights * (float)($room->base_price ?? 0), 2);
            }

            // If event_date (function room) — fallback to base_price
            return round((float)($room->base_price ?? 0), 2);
        }

        // Service booking calculation
        if (method_exists($booking, 'service') && $booking->service) {
            $service = $booking->service;
            $guests = max(1, (int)($booking->number_of_guests ?? 1));
            // If per_person pricing is handled in UI, you'd multiply by guests
            return round($guests * (float)($service->base_price ?? 0), 2);
        }

        // Fallback
        return 0.00;
    }

    /**
     * Remaining balance = total - completed payments
     */
    public static function remainingBalance($booking): float
    {
        $total = self::computeTotal($booking);
        $completed = (float) ($booking->totalPaymentsCompleted() ?? 0);
        $remaining = round(max(0, $total - $completed), 2);
        return $remaining;
    }
}
