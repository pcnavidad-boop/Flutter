<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Validate essential fields
        if (
            empty($payload['metadata']['booking_type']) ||
            empty($payload['metadata']['booking_id'])   ||
            empty($payload['status'])                   ||
            empty($payload['amount'])
        ) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        $bookingType = $payload['metadata']['booking_type']; 
        $bookingId   = $payload['metadata']['booking_id'];
        $status      = strtolower($payload['status']);       
        $amount      = $payload['amount'] / 100;             // convert from centavos if needed

        $reference = $payload['id'] ?? null;

        // Identify booking
        $booking = PaymentService::findBooking($bookingType, $bookingId);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Create online payment entry
        $booking->payments()->create([
            'user_id'   => null,
            'reference' => $reference,
            'amount'    => $amount,
            'date'      => now(),
            'method'    => 'api',       
            'channel'   => 'online',    
            'status'    => $status,
        ]);

        // Recompute payment state
        PaymentService::updateBookingPaymentStatus($booking);

        return response()->json(['success' => true], 200);
    }
}
