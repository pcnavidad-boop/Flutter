<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class BookingCalculator
{
    /**
     * MAIN ENTRY POINT
     */
    public static function computeTotal($booking): float
    {
        try {
            return match (true) {
                $booking instanceof RoomBooking    => self::computeRoom($booking),
                $booking instanceof ServiceBooking => self::computeService($booking),
                default                            => 0.00
            };
        } catch (\Throwable $e) {
            Log::error('BookingCalculator::computeTotal failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id ?? null,
            ]);
            return 0.00;
        }
    }

    /**
     * ROOM BOOKING CALCULATION
     *
     * - per_night -> charge per night (diffInDays: end - start)
     * - per_day -> inclusive days (start..end inclusive)
     */
    private static function computeRoom(RoomBooking $booking): float
    {
        try {
            $room = $booking->room;

            if (!$room || !$booking->start_date || !$booking->end_date) {
                return 0.00;
            }

            $base = (float) $room->base_price;
            $priceType = trim((string) $room->price_type);

            $nights = self::countNights($booking->start_date, $booking->end_date);
            $inclusiveDays = self::countInclusiveDays($booking->start_date, $booking->end_date);

            return match ($priceType) {
                'per_night'     => round($nights * $base, 2),
                'per_day'       => round($inclusiveDays * $base, 2),
                default         => round($inclusiveDays * $base, 2),
            };
        } catch (\Throwable $e) {
            Log::error('BookingCalculator::computeRoom error', [
                'message' => $e->getMessage(),
                'booking_id' => $booking->id ?? null,
            ]);
            return 0.00;
        }
    }

    /**
     * Count nights (exclusive): end_date - start_date
     * Ensures minimum of 1 night.
     */
    private static function countNights($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $diff = $start->diffInDays($end); 
        return max(1, (int) $diff);
    }

    /**
     * Count inclusive days (start..end inclusive)
     */
    private static function countInclusiveDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $diff = $start->diffInDays($end); 
        return max(1, (int) $diff + 1); 
    }

    /**
     * SERVICE BOOKING CALCULATION
     */
    private static function computeService(ServiceBooking $booking): float
    {
        try {
            $service = $booking->service;
            if (!$service) return 0.00;

            $base   = (float) $service->base_price;
            $guests = max(1, (int) $booking->number_of_guests);

            // Always per person
            return round($base * $guests, 2);

        } catch (\Throwable $e) {
            Log::error('BookingCalculator::computeService error', [
                'message' => $e->getMessage(),
                'booking_id' => $booking->id ?? null,
            ]);
            return 0.00;
        }
    }

    /**
     * REMAINING BALANCE
     */
    public static function remainingBalance($booking): float
    {
        try {
            $total = self::computeTotal($booking);

            $paid     = (float) $booking->totalPaymentsCompleted();
            $refunded = (float) $booking->totalPaymentsRefunded();

            return round(max(0, $total - max(0, $paid - $refunded)), 2);
        } catch (\Throwable $e) {
            Log::error('BookingCalculator::remainingBalance error', [
                'message' => $e->getMessage(),
                'booking_id' => $booking->id ?? null,
            ]);
            return 0.00;
        }
    }
}