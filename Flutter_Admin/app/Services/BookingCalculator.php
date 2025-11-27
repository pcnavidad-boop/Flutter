<?php

namespace App\Services;

use Carbon\Carbon;

class BookingCalculator
{
    // Compute total cost of any booking type.
    public static function computeTotal($booking): float
    {
        if (method_exists($booking, 'room') && $booking->room) {
            return self::computeRoomTotal($booking);
        }

        if (method_exists($booking, 'service') && $booking->service) {
            return self::computeServiceTotal($booking);
        }

        return 0;
    }

    // ROOM BOOKING CALCULATION
    private static function computeRoomTotal($booking): float
    {
        $room = $booking->room;
        $base = (float) $room->base_price;

        // Stay rooms (per night)
        if ($room->price_type === 'per_night') {

            if (!$booking->start_date || !$booking->end_date) {
                return 0;
            }

            $start = Carbon::parse($booking->start_date);
            $end   = Carbon::parse($booking->end_date);

            if ($end->lte($start)) { 
                return 0;
            }

            $nights = max(1, $end->diffInDays($start));

            return round($nights * $base, 2);
        }

        // Function room (per event per day)
        if ($room->price_type === 'per_event_per_day') {

            if (!$booking->start_date || !$booking->end_date) {
                return 0;
            }

            $start = Carbon::parse($booking->start_date);
            $end   = Carbon::parse($booking->end_date);

            if ($end->lt($start)) {
                return 0;
            }

            // Event rooms charge per day inclusive
            $days = $end->diffInDays($start) + 1;

            return round($days * $base, 2);
        }

        return 0;
    }

    // SERVICE BOOKING CALCULATION
    private static function computeServiceTotal($booking): float
    {
        $service = $booking->service;
        $base    = (float) $service->base_price;
        $guests  = max(1, (int) $booking->number_of_guests);

        switch ($service->price_type) {

            case 'per_person':
                return round($base * $guests, 2);

            case 'per_day':
                return round($base, 2);

            case 'per_hour':
                if (!$booking->start_time || !$booking->end_time) {
                    return round($base, 2);
                }

                $start = Carbon::parse($booking->start_time);
                $end   = Carbon::parse($booking->end_time);

                if ($end->lte($start)) {
                    return round($base, 2);
                }

                $hours = max(1, $end->diffInHours($start));

                return round($base * $hours, 2);

            default:
                return round($base, 2);
        }
    }

    // Remaining balance logic
    public static function remainingBalance($booking): float
    {
        $total = self::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();

        $net = $paid - $refunded;

        return round(max(0, $total - $net), 2);
    }
}
