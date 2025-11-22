<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use Stripe\Webhook;

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
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Only handle successful payment
        if ($event->type === 'checkout.session.completed') {

            $session = $event->data['object'];

            // Stripe stores metadata inside the PaymentIntent
            $bookingType = $session['metadata']['booking_type'];
            $bookingId   = $session['metadata']['booking_id'];
            $amount      = $session['amount_total'] / 100; // correct

            // Locate booking
            $booking = PaymentService::findBooking($bookingType, $bookingId);

            if (!$booking) {
                \Log::error('Booking not found in webhook', [
                    'booking_type' => $bookingType,
                    'booking_id' => $bookingId
                ]);
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Save payment
            $booking->payments()->create([
                'user_id'   => null,
                'reference' => $session['id'],
                'amount'    => $amount,
                'date'      => now(),
                'method'    => 'api',
                'channel'   => 'online',
                'status'    => 'completed',
            ]);

            PaymentService::updateBookingPaymentStatus($booking);
        }

        return response()->json(['success' => true]);
    }
}
