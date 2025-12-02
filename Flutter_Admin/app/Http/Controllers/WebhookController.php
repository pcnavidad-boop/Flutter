<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentService;
use App\Notifications\PaymentReceiptNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingBecameConfirmedAdminNotification;
use App\Models\User;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // 1. Validate Stripe signature
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error("[Stripe] Invalid signature", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['ignored' => true], 200);
        }

        $session = $event->data['object'];

        // 2. Metadata validation
        $type      = $session['metadata']['booking_type']      ?? null;
        $reference = $session['metadata']['booking_reference'] ?? null;
        $sessionId = $session['id'];

        if (!$type || !$reference) {
            Log::error("[Stripe] Missing metadata");
            return response()->json(['error' => 'Missing metadata'], 400);
        }

        // 3. Retrieve booking
        $booking = PaymentService::findBookingByReference($type, $reference);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // 4. Prevent duplicate payments
        if ($booking->payments()->where('reference', $sessionId)->exists()) {
            return response()->json(['ignored' => true], 200);
        }

        // 5. Validate amount
        $amount = ($session['amount_total'] ?? 0) / 100;
        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // 6. Disallow payments for invalid booking statuses
        if (in_array($booking->booking_status, ['cancelled', 'completed', 'checked_out'])) {
            return response()->json(['error' => 'Invalid booking status'], 400);
        }

        // 7. Validate associated item (room/service)
        $item = $type === 'room' ? $booking->room : $booking->service;
        if (!$item || $item->is_archived || $item->status === 'maintenance') {
            return response()->json(['error' => 'Item unavailable'], 400);
        }

        // 8. Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) {
            return response()->json(['ignored' => true], 200);
        }
        $amount = min($amount, $remaining);

        // Track old state for notification logic
        $wasPending = ($booking->booking_status === 'pending');

        // 9. Record payment
        $booking->payments()->create([
            'reference'    => $sessionId,
            'amount'       => $amount,
            'method'       => 'api',
            'channel'      => 'online',
            'status'       => 'completed',
            'processed_by' => null,
            'paid_at'      => now(),
        ]);

        // 10. Update booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        // If the booking was pending and now paid enough to become confirmed
        if ($wasPending && $booking->booking_status === 'confirmed') {

            // Notify guest booking confirmation
            try {
                $booking->notify(new BookingConfirmedNotification($booking));
            } catch (\Throwable $e) {
                Log::error("[Stripe] Failed sending guest booking confirmation", [
                    'error' => $e->getMessage(),
                ]);
            }

            // Notify admins of new confirmed booking
            User::where('role', 'admin')->each(function($admin) use ($booking) {
                try {
                    $admin->notify(new BookingBecameConfirmedAdminNotification($booking));
                } catch (\Throwable $e) {
                    Log::error("[Stripe] Failed sending admin notification", [
                        'error' => $e->getMessage(),
                    ]);
                }
            });
        }

        // Always send payment receipt to guest
        try {
            $booking->notify(new PaymentReceiptNotification($booking, $amount));
        } catch (\Throwable $e) {
            Log::error("[Stripe] Failed sending payment receipt", [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true], 200);
    }
}
