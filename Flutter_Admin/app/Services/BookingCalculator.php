<?php

namespace App\Services;

use Carbon\Carbon;

class BookingCalculator
{
    // Compute total cost of a booking
    public static function computeTotal($booking): float
    {
        // ROOM BOOKING
        if (method_exists($booking, 'room') && $booking->room) {
            return self::computeRoomTotal($booking);
        }

        // SERVICE BOOKING
        if (method_exists($booking, 'service') && $booking->service) {
            return self::computeServiceTotal($booking);
        }

        return 0;
    }


    // Compute room booking cost
    private static function computeRoomTotal($booking): float
    {
        $room = $booking->room;
        $base = (float) ($room->base_price ?? 0);

        // Stay rooms (per night)
        if ($booking->check_in_date && $booking->check_out_date) {
            $start = Carbon::parse($booking->check_in_date);
            $end   = Carbon::parse($booking->check_out_date);

            if ($end->lte($start)) {
                return 0;
            }

            $nights = max(1, $end->diffInDays($start));
            return round($base * $nights, 2);
        }

        // Function rooms (per event per day, inclusive)
        if ($booking->event_start_date && $booking->event_end_date) {
            $start = Carbon::parse($booking->event_start_date);
            $end   = Carbon::parse($booking->event_end_date);

            if ($end->lt($start)) {
                return 0;
            }

            $days = ($end->diffInDays($start) + 1);
            return round($base * $days, 2);
        }

        // No dates → fallback
        return round($base, 2);
    }


    // Compute service booking cost
    private static function computeServiceTotal($booking): float
    {
        $service = $booking->service;
        $base    = (float) ($service->base_price ?? 0);
        $guests  = max(1, (int) $booking->number_of_guests);

        switch ($service->price_type) {

            case 'per_person':
                return round($base * $guests, 2);

            case 'per_service':
                return round($base, 2);

            case 'per_hour':
                // If no valid times → fallback to base
                if (!$booking->start_time || !$booking->end_time) {
                    return round($base, 2);
                }

                $start = Carbon::parse($booking->start_time);
                $end   = Carbon::parse($booking->end_time);

                if ($end->lte($start)) {
                    return round($base, 2);
                }

                // WHOLE hours only (no fractions), min 1 hour
                $hours = max(1, $end->diffInHours($start));

                return round($base * $hours, 2);

            default:
                return round($base, 2);
        }
    }


    // Calculate remaining balance
    public static function remainingBalance($booking): float
    {
        $total = self::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();

        $net = $paid - $refunded;

        return round(max(0, $total - $net), 2);
    }
}
