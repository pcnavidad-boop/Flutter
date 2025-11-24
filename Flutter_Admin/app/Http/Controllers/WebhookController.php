<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use App\Notifications\GuestPaymentReceivedNotification;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            // Validate Stripe signature
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error("Stripe signature verification failed", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Only respond to checkout completion
        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['ignored' => true], 200);
        }

        $session = $event->data['object'];

        $bookingType = $session['metadata']['booking_type'] ?? null;
        $reference   = $session['metadata']['booking_reference'] ?? null;

        if (!$bookingType || !$reference) {
            Log::error("Stripe webhook missing metadata", $session);
            return response()->json(['error' => 'Missing metadata'], 400);
        }

        // Find booking by reference
        $booking = PaymentService::findBookingByReference($bookingType, $reference);

        if (!$booking) {
            Log::error("Booking not found via reference", ['reference' => $reference]);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Amount is in cents
        $amount = ($session['amount_total'] ?? 0) / 100;

        // Create payment record
        $booking->payments()->create([
            'user_id'   => null,
            'reference' => $session['id'], // Stripe session ID
            'amount'    => $amount,
            'date'      => now(),
            'method'    => 'api',
            'channel'   => 'online',
            'status'    => 'completed',
        ]);

        // Update booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        // Send email to guest
        try {
            $booking->notify(new GuestPaymentReceivedNotification($booking, $amount));
        } catch (\Exception $e) {
            Log::error("Failed to send guest payment email", [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
