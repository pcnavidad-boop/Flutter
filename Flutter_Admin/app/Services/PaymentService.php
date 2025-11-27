<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use App\Services\BookingCalculator;

class PaymentService
{
    // Validate booking reference format
    public static function isRoomReference(string $reference): bool
    {
        return preg_match('/^RB-[A-Z0-9]{8}$/', $reference) === 1;
    }

    public static function isServiceReference(string $reference): bool
    {
        return preg_match('/^SB-[A-Z0-9]{8}$/', $reference) === 1;
    }

    public static function detectBookingType(string $reference): ?string
    {
        if (self::isRoomReference($reference)) return 'room';
        if (self::isServiceReference($reference)) return 'service';
        return null;
    }


    // Find booking via reference
    public static function findBookingByReference(string $type, string $reference)
    {
        if ($type === 'room' && !self::isRoomReference($reference)) {
            return null;
        }

        if ($type === 'service' && !self::isServiceReference($reference)) {
            return null;
        }

        return match ($type) {
            'room'    => RoomBooking::where('reference', $reference)->first(),
            'service' => ServiceBooking::where('reference', $reference)->first(),
            default   => null,
        };
    }


    // Update booking payment status based on payments
    public static function updateBookingPaymentStatus($booking): void
    {
        $total = BookingCalculator::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();

        $netPaid = max(0, $paid - $refunded);

        // FULLY REFUNDED
        if ($netPaid == 0 && $refunded > 0) {
            $booking->payment_status = 'refunded';
            $booking->save();
            return;
        }

        // FULLY PAID
        if ($netPaid >= $total && $total > 0) {
            $booking->payment_status = 'fully_paid';
            $booking->save();
            return;
        }

        // DOWNPAYMENT MADE
        if ($netPaid > 0) {
            $booking->payment_status = 'downpayment';
            $booking->save();
            return;
        }

        // DEFAULT
        $booking->payment_status = 'downpayment';
        $booking->save();
    }


    // 30% Downpayment calculation
    public static function requiredDownpayment($booking): float
    {
        $total = BookingCalculator::computeTotal($booking);

        return round($total * 0.30, 2);
    }


    // Remaining balance
    public static function remainingBalance($booking): float
    {
        return BookingCalculator::remainingBalance($booking);
    }
}
