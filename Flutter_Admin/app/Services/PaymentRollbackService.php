<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRollbackService
{
    /**
     * Safely rollback (delete) an offline payment.
     *
     * Throws \Exception on violation.
     */
    public static function rollback(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {

            $booking = $payment->payable;

            // Reject Stripe/API payments
            if ($payment->method === 'api') {
                throw new \Exception('Stripe (API) payments cannot be deleted.');
            }

            // Refunded payments cannot be rolled back
            if ($payment->status === 'refunded') {
                throw new \Exception('Refunded payments cannot be rolled back.');
            }

            // Booking lifecycle restrictions
            if (in_array($booking->booking_status, ['completed', 'checked_out', 'cancelled'])) {
                throw new \Exception('Cannot rollback payments for completed or cancelled bookings.');
            }

            // Cannot rollback if item is archived
            if (method_exists($booking->payable, 'is_archived') &&
                $booking->payable->is_archived) {
                throw new \Exception('Cannot rollback payments for archived items.');
            }

            // At this point, delete is permitted — delete and then recalc booking payment status
            $payment->delete();

            // Recalculate booking payment status
            PaymentService::updateBookingPaymentStatus($booking);
        });
    }
}
