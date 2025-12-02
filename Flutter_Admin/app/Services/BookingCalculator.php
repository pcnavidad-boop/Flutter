<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class BookingCalculator
{
    public static function computeTotal($booking): float
    {
        if ($booking instanceof RoomBooking) {
            return self::computeRoomTotal($booking);
        }

        if ($booking instanceof ServiceBooking) {
            return self::computeServiceTotal($booking);
        }

        return 0.00;
    }

    private static function computeRoomTotal(RoomBooking $booking): float
    {
        $room = $booking->room;
        if (!$room) return 0.00;

        $base = (float) $room->base_price;

        if (!$booking->start_date || !$booking->end_date) return 0.00;

        $start = Carbon::parse($booking->start_date);
        $end   = Carbon::parse($booking->end_date);

        if ($end->lte($start)) return 0.00;

        if ($room->price_type === 'per_night') {
            $nights = max(1, $end->diffInDays($start));
            return round($nights * $base, 2);
        }

        if ($room->price_type === 'per_event_per_day') {
            $days = $end->diffInDays($start) + 1;
            return round($days * $base, 2);
        }

        return 0.00;
    }

    private static function computeServiceTotal(ServiceBooking $booking): float
    {
        $service = $booking->service;
        if (!$service) return 0.00;

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

                try {
                    $start = Carbon::parse($booking->start_time);
                    $end   = Carbon::parse($booking->end_time);
                } catch (\Throwable $e) {
                    return round($base, 2);
                }

                if ($end->lte($start)) return round($base, 2);

                $minutes = $end->diffInMinutes($start);
                $hours   = max(1, (int) ceil($minutes / 60));

                return round($base * $hours, 2);
        }

        return round($base, 2);
    }

    public static function remainingBalance($booking): float
    {
        $total = self::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();
        $netPaid  = max(0, $paid - $refunded);

        return round(max(0, $total - $netPaid), 2);
    }
}
