<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class PaymentService
{
    /**
     * Recalculate the booking's payment status
     * using the new centralized BookingCalculator engine.
     */
    public static function updateBookingPaymentStatus($booking): void
    {
        $total = BookingCalculator::computeTotal($booking);
        $paid  = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();

        // Case 1 — Fully refunded
        if ($paid == 0 && $refunded > 0) {
            $booking->payment_status = 'refunded';
            $booking->booking_status = 'cancelled'; // logical default
            $booking->save();
            return;
        }

        // Case 2 — Fully paid
        if ($paid >= $total && $total > 0) {
            $booking->payment_status = 'fully_paid';
            $booking->save();
            return;
        }

        // Case 3 — Partial payment
        if ($paid > 0) {
            $booking->payment_status = 'downpayment';
            $booking->save();
            return;
        }

        // Case 4 — No payments
        $booking->payment_status = 'downpayment';
        $booking->save();
    }

    /**
     * Downpayment rule (editable)
     * Example: 30%
     */
    public static function requiredDownpayment($booking): float
    {
        $total = BookingCalculator::computeTotal($booking);
        $percentage = 0.30;

        return round($total * $percentage, 2);
    }

    /**
     * Check if Stripe payment meets minimum downpayment.
     */
    public static function paymentMeetsDownpayment($booking, float $amount): bool
    {
        return $amount >= self::requiredDownpayment($booking);
    }

    /**
     * Find booking via ID (admin-side use)
     */
    public static function findBooking(string $type, int $id)
    {
        return match ($type) {
            'room'    => RoomBooking::findOrFail($id),
            'service' => ServiceBooking::findOrFail($id),
            default   => null,
        };
    }

    /**
     * Find booking via reference (Stripe-side use)
     */
    public static function findBookingByReference(string $type, string $reference)
    {
        return match ($type) {
            'room'    => RoomBooking::where('reference', $reference)->first(),
            'service' => ServiceBooking::where('reference', $reference)->first(),
            default   => null,
        };
    }

    /**
     * Get remaining balance using the new BookingCalculator logic.
     */
    public static function remainingBalance($booking): float
    {
        return BookingCalculator::remainingBalance($booking);
    }
}
