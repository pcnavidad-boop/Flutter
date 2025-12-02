<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRollbackService
{
    public static function rollback(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {

            $booking = $payment->payable;

            if (!$booking) {
                throw new \Exception('Associated booking not found.');
            }

            if ($payment->method === 'api') {
                throw new \Exception('API payments cannot be deleted.');
            }

            if ($payment->status === 'refunded') {
                throw new \Exception('Refunded payments cannot be rolled back.');
            }

            if (in_array($booking->booking_status, ['completed','checked_out','cancelled'])) {
                throw new \Exception('Cannot rollback payments for finalized bookings.');
            }

            $item = $booking instanceof \App\Models\RoomBooking ? $booking->room :
                    ($booking instanceof \App\Models\ServiceBooking ? $booking->service : null);

            if ($item && isset($item->is_archived) && $item->is_archived) {
                throw new \Exception('Cannot rollback payment for an archived item.');
            }

            $payment->delete();

            PaymentService::updateBookingPaymentStatus($booking);
        });
    }
}
