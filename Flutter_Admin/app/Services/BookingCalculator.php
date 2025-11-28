<?php

namespace App\Services;

use Carbon\Carbon;

class BookingCalculator
{
    // Compute total cost of any booking type
    public static function computeTotal($booking): float
    {
        if (method_exists($booking, 'room') && $booking->room) {
            return self::computeRoomTotal($booking);
        }

        if (method_exists($booking, 'service') && $booking->service) {
            return self::computeServiceTotal($booking);
        }

        return 0.00;
    }

    // Compute total for room bookings (stay rooms & function rooms)
    private static function computeRoomTotal($booking): float
    {
        $room = $booking->room;
        $base = (float) ($room->base_price ?? 0);

        if (!$booking->start_date || !$booking->end_date) {
            return 0.00;
        }

        $start = Carbon::parse($booking->start_date);
        $end   = Carbon::parse($booking->end_date);

        if ($end->lte($start)) {
            return 0.00;
        }

        // Per-night stay rooms
        if ($room->price_type === 'per_night') {
            $nights = max(1, $end->diffInDays($start));
            return round($nights * $base, 2);
        }

        // Function hall – per day inclusive
        if ($room->price_type === 'per_event_per_day') {
            $days = $end->diffInDays($start) + 1;
            return round($days * $base, 2);
        }

        return 0.00;
    }

    // Compute total for services
    private static function computeServiceTotal($booking): float
    {
        $service = $booking->service;
        $base    = (float) ($service->base_price ?? 0);
        $guests  = max(1, (int) $booking->number_of_guests);

        switch ($service->price_type) {

            case 'per_person':
                return round($base * $guests, 2);

            case 'per_day':
                // per_day is flat rate regardless of guests (adjust later if needs change)
                return round($base, 2);

            case 'per_hour':
                // Use minutes, then round up to whole hours (common business rule)
                if (!$booking->start_time || !$booking->end_time) {
                    return round($base, 2);
                }

                $start = Carbon::createFromFormat('H:i', $booking->start_time);
                $end   = Carbon::createFromFormat('H:i', $booking->end_time);

                if ($end->lte($start)) {
                    return round($base, 2);
                }

                $minutes = $end->diffInMinutes($start);
                $hours = max(1, (int) ceil($minutes / 60)); // whole hours, min 1

                return round($base * $hours, 2);

            default:
                return round($base, 2);
        }
    }

    // Compute remaining balance
    public static function remainingBalance($booking): float
    {
        $total = self::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();

        $netPaid = max(0, $paid - $refunded);

        return round(max(0, $total - $netPaid), 2);
    }
}
