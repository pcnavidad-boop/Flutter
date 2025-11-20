<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class PaymentService
{
    // Recalculate booking payment status based on all payments.
    public static function updateBookingPaymentStatus($booking): void
    {
        $completed = $booking->totalPaymentsCompleted();
        $refunded  = $booking->totalPaymentsRefunded();
        $totalPrice = $booking->total_price ?? 0;

        // Case 1 — fully refunded
        if ($completed == 0 && $refunded > 0) {
            $booking->payment_status = 'refunded';
            $booking->booking_status = 'cancelled'; // optional but logical
            $booking->save();
            return;
        }

        // Case 2 — fully paid
        if ($completed >= $totalPrice && $totalPrice > 0) {
            $booking->payment_status = 'fully_paid';
            $booking->save();
            return;
        }

        // Case 3 — partial / downpayment
        if ($completed > 0) {
            $booking->payment_status = 'downpayment';
            $booking->save();
            return;
        }

        // Case 4 — no payments at all
        $booking->payment_status = 'downpayment';
        $booking->save();
    }

    /**
     * Determine the required downpayment amount.
     * 
     * Example: 30% downpayment rule (you can adjust this).
     */
    public static function requiredDownpayment($booking): float
    {
        $totalPrice = $booking->total_price ?? 0;
        $percentage = 0.30; // 30% downpayment requirement

        return round($totalPrice * $percentage, 2);
    }

    /**
     * Guest-side: Validate if API payment meets minimum downpayment requirement.
     */
    public static function paymentMeetsDownpayment($booking, float $amount): bool
    {
        return $amount >= self::requiredDownpayment($booking);
    }

    /**
     * Helper: Identify the correct booking model for polymorphic operations.
     */
    public static function findBooking(string $type, int $id)
    {
        return match ($type) {
            'room'    => RoomBooking::findOrFail($id),
            'service' => ServiceBooking::findOrFail($id),
            default   => null,
        };
    }
}
