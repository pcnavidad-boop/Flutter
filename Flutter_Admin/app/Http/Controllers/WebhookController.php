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
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // 1. Validate signature
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error("[Stripe] Invalid signature", [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Only process checkout completion
        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['ignored' => true], 200);
        }

        $session = $event->data['object'];

        // 2. Validate metadata
        $type      = $session['metadata']['booking_type']      ?? null;
        $reference = $session['metadata']['booking_reference'] ?? null;
        $sessionId = $session['id'];

        if (!$type || !$reference) {
            Log::error("[Stripe] Missing metadata", $session);
            return response()->json(['error' => 'Missing metadata'], 400);
        }

        // 3. Find booking
        $booking = PaymentService::findBookingByReference($type, $reference);
        if (!$booking) {
            Log::error("[Stripe] Booking not found", ['reference' => $reference]);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // 4. Prevent duplicate processing
        if ($booking->payments()->where('reference', $sessionId)->exists()) {
            Log::info("[Stripe] Duplicate webhook ignored", ['reference' => $reference]);
            return response()->json(['ignored' => true], 200);
        }

        // 5. Validate amount
        $amount = ($session['amount_total'] ?? 0) / 100;

        if ($amount <= 0) {
            Log::error("[Stripe] Invalid amount", [
                'amount_total' => $session['amount_total'] ?? null
            ]);
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // 6. Validate booking status
        if (in_array($booking->booking_status, [
            'cancelled', 'completed', 'checked_out'
        ])) {
            Log::warning("[Stripe] Payment rejected - invalid booking status", [
                'reference' => $reference,
                'status' => $booking->booking_status
            ]);
            return response()->json(['error' => 'Invalid booking status'], 400);
        }

        // 7. Validate linked item (room or service)
        $item = $type === 'room' ? $booking->room : $booking->service;

        if ($item->is_archived) {
            Log::warning("[Stripe] Payment rejected - archived item", [
                'reference' => $reference
            ]);
            return response()->json(['error' => 'Item archived'], 400);
        }

        if ($item->status === 'maintenance') {
            Log::warning("[Stripe] Payment rejected - item under maintenance", [
                'reference' => $reference
            ]);
            return response()->json(['error' => 'Item under maintenance'], 400);
        }

        // 8. Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) {
            Log::info("[Stripe] Already fully paid - ignored", [
                'reference' => $reference
            ]);
            return response()->json(['ignored' => true], 200);
        }

        if ($amount > $remaining) {
            $amount = $remaining; // clamp amount
        }

        // 9. Record the Stripe payment
        try {
            $booking->payments()->create([
                'reference'    => $sessionId,        // unique Stripe session id
                'amount'       => $amount,
                'method'       => 'api',
                'channel'      => 'online',
                'status'       => 'completed',
                'processed_by' => null,
                'paid_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("[Stripe] Failed to record payment", [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to record payment'], 500);
        }

        // 10. Update payment status
        PaymentService::updateBookingPaymentStatus($booking);

        // 11. Notify guest
        try {
            $booking->notify(new GuestPaymentReceivedNotification($booking, $amount));
        } catch (\Throwable $e) {
            Log::error("[Stripe] Failed to email guest", [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true], 200);
    }
}
