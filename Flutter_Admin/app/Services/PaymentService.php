<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;

class PaymentService
{
    public static function isRoomReference(string $ref): bool
    {
        return preg_match('/^RB-[A-Z0-9]{8}$/', $ref) === 1;
    }

    public static function isServiceReference(string $ref): bool
    {
        return preg_match('/^SB-[A-Z0-9]{8}$/', $ref) === 1;
    }

    public static function detectBookingType(string $reference): ?string
    {
        return match (true) {
            self::isRoomReference($reference)    => 'room',
            self::isServiceReference($reference) => 'service',
            default => null,
        };
    }

    public static function findBookingByReference(string $type, string $reference)
    {
        return match ($type) {
            'room'    => RoomBooking::where('reference', $reference)->first(),
            'service' => ServiceBooking::where('reference', $reference)->first(),
            default   => null,
        };
    }

    public static function updateBookingPaymentStatus($booking): void
    {
        if (!$booking) return;

        $total = BookingCalculator::computeTotal($booking);

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();
        $netPaid  = max(0, $paid - $refunded);

        if ($total == 0) {
            $booking->payment_status = 'fully_paid';
        } elseif ($refunded >= $total && $netPaid == 0) {
            $booking->payment_status = 'refunded';
        } elseif ($netPaid >= $total) {
            $booking->payment_status = 'fully_paid';
        } elseif ($netPaid > 0) {
            $booking->payment_status = 'downpayment';
        } else {
            $booking->payment_status = 'unpaid';
        }

        $booking->save();
    }

    public static function requiredDownpayment($booking): float
    {
        $total     = BookingCalculator::computeTotal($booking);
        $remaining = BookingCalculator::remainingBalance($booking);

        $down = round($total * 0.30, 2);

        return min($down, $remaining);
    }

    public static function remainingBalance($booking): float
    {
        return BookingCalculator::remainingBalance($booking);
    }
}
