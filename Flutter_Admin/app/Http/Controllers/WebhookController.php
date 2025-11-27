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

        // Validate Stripe Signature 
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error("[Stripe] Invalid signature", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // We only accept completed checkout sessions
        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['ignored' => true], 200);
        }

        $session = $event->data['object'];

        // Validate metadata
        $type      = $session['metadata']['booking_type'] ?? null;
        $reference = $session['metadata']['booking_reference'] ?? null;

        if (!$type || !$reference) {
            Log::error("[Stripe] Missing metadata", $session);
            return response()->json(['error' => 'Missing metadata'], 400);
        }

        // Find booking
        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            Log::error("[Stripe] Booking not found", ['reference' => $reference]);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Business Rule: Prevent payments for invalid booking states
        if (in_array($booking->booking_status, ['cancelled', 'completed', 'checked_out'])) {
            Log::warning("[Stripe] Payment rejected - invalid booking status", [
                'reference' => $reference,
                'status' => $booking->booking_status
            ]);
            return response()->json(['error' => 'Payment rejected due to booking status'], 400);
        }

        // Room/Service archived?
        if (method_exists($booking->payable, 'is_archived') && $booking->payable->is_archived) {
            Log::warning("[Stripe] Payment rejected - archived item", [
                'reference' => $reference,
            ]);
            return response()->json(['error' => 'Cannot pay for archived item'], 400);
        }

        // Room/Service under maintenance?
        if (method_exists($booking->payable, 'status') && $booking->payable->status === 'maintenance') {
            Log::warning("[Stripe] Payment rejected - item under maintenance", [
                'reference' => $reference,
            ]);
            return response()->json(['error' => 'Cannot pay while item under maintenance'], 400);
        }

        // Extract Stripe amount → convert to PHP decimal
        $amount = ($session['amount_total'] ?? 0) / 100;

        if ($amount <= 0) {
            Log::error("[Stripe] Invalid amount", ['amount_total' => $session['amount_total'] ?? null]);
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) {
            Log::warning("[Stripe] Payment ignored: booking already fully paid", ['reference' => $reference]);
            return response()->json(['ignored' => true], 200);
        }

        if ($amount > $remaining) {
            $amount = $remaining; 
        }

        // Record Payment
        try {
            $booking->payments()->create([
                'reference'    => $session['id'],   
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
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to record payment'], 500);
        }

        // Update booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        // Notify Guest
        try {
            $booking->notify(new GuestPaymentReceivedNotification($booking, $amount));
        } catch (\Throwable $e) {
            Log::error("[Stripe] Failed to email guest", [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true], 200);
    }
}
