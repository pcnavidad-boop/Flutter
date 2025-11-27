<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentService;
use App\Notifications\GuestPaymentReceivedNotification;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error("Stripe signature verification failed", [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['ignored' => true], 200);
        }

        $session = $event->data['object'];

        // Validate metadata
        $type = $session['metadata']['booking_type'] ?? null;
        $reference = $session['metadata']['booking_reference'] ?? null;

        if (!$type || !$reference) {
            Log::error("Webhook missing metadata", $session);
            return response()->json(['error' => 'Missing metadata'], 400);
        }

        // Find booking by reference
        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            Log::error("Booking not found for webhook", ['reference' => $reference]);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Get amount in PHP format
        $amount = ($session['amount_total'] ?? 0) / 100;

        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);

        if ($amount > $remaining) {
            $amount = $remaining;
        }

        // Record payment
        $booking->payments()->create([
            'user_id'   => null,
            'reference' => $session['id'],
            'amount'    => $amount,
            'method'    => 'api',
            'channel'   => 'online',
            'status'    => 'completed',
        ]);

        // Update booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        // Notify guest
        try {
            $booking->notify(new GuestPaymentReceivedNotification($booking, $amount));
        } catch (\Throwable $e) {
            Log::error("Failed to notify guest", ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }
}
