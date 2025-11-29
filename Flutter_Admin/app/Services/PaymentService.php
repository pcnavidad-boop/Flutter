<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class PaymentService
{
    // Validate booking reference prefixes
    public static function isRoomReference(string $ref): bool
    {
        return preg_match('/^RB-[A-Z0-9]{8}$/', $ref) === 1;
    }

    public static function isServiceReference(string $ref): bool
    {
        return preg_match('/^SB-[A-Z0-9]{8}$/', $ref) === 1;
    }

    // Auto-detect type by reference
    public static function detectBookingType(string $reference): ?string
    {
        return match (true) {
            self::isRoomReference($reference)    => 'room',
            self::isServiceReference($reference) => 'service',
            default => null,
        };
    }

    // Lookup booking via reference
    public static function findBookingByReference(string $type, string $reference)
    {
        if ($type === 'room' && !self::isRoomReference($reference)) return null;
        if ($type === 'service' && !self::isServiceReference($reference)) return null;

        return match ($type) {
            'room'    => RoomBooking::where('reference', $reference)->first(),
            'service' => ServiceBooking::where('reference', $reference)->first(),
            default   => null,
        };
    }

    // Update payment status
    public static function updateBookingPaymentStatus($booking): void
    {
        if (!$booking) return;

        // Use stored total_price when available (optimization)
        $total = $booking->total_price ?? BookingCalculator::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();
        $netPaid  = max(0, $paid - $refunded);

        // Handle zero-total bookings
        if ($total == 0) {
            $booking->payment_status = 'fully_paid';
            $booking->save();
            return;
        }

        // Fully refunded
        if ($refunded >= $total && $netPaid == 0) {
            $booking->payment_status = 'refunded';
            $booking->save();
            return;
        }

        // Fully paid
        if ($netPaid >= $total) {
            $booking->payment_status = 'fully_paid';
            $booking->save();
            return;
        }

        // Partial (downpayment)
        if ($netPaid > 0) {
            $booking->payment_status = 'downpayment';
            $booking->save();
            return;
        }

        // No payments
        $booking->payment_status = 'downpayment';
        $booking->save();
    }

    // Calculates required downpayment (30%) but capped by remaining balance
    public static function requiredDownpayment($booking): float
    {
        $total = BookingCalculator::computeTotal($booking);
        $remaining = self::remainingBalance($booking);

        $downpayment = round($total * 0.30, 2);

        return min($downpayment, $remaining);
    }

    // Remaining balance wrapper
    public static function remainingBalance($booking): float
    {
        return BookingCalculator::remainingBalance($booking);
    }
}
