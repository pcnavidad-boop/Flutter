<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\PaymentService;

class StripeCheckoutController extends Controller
{
    /**
     * Handle clicking "Pay Downpayment"
     */
    public function payDownpayment($reference)
    {
        $type = $this->detectBookingType($reference);
        $booking = PaymentService::findBookingByReference($type, $reference);

        $amount = PaymentService::requiredDownpayment($booking);

        // Prevent paying if already fully paid
        if (PaymentService::remainingBalance($booking) <= 0) {
            return back()->with('error', 'This booking is already fully paid.');
        }

        return $this->startStripeCheckout($booking, $type, $amount);
    }

    /**
     * Handle clicking "Pay Full Amount"
     */
    public function payFull($reference)
    {
        $type = $this->detectBookingType($reference);
        $booking = PaymentService::findBookingByReference($type, $reference);

        $amount = \App\Services\BookingCalculator::computeTotal($booking);

        if (PaymentService::remainingBalance($booking) <= 0) {
            return back()->with('error', 'This booking is already fully paid.');
        }

        return $this->startStripeCheckout($booking, $type, $amount);
    }

    /**
     * Handle clicking "Pay Remaining Balance"
     */
    public function payRemaining($reference)
    {
        $type = $this->detectBookingType($reference);
        $booking = PaymentService::findBookingByReference($type, $reference);

        $remaining = PaymentService::remainingBalance($booking);

        if ($remaining <= 0) {
            return back()->with('error', 'This booking is already fully paid.');
        }

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    /**
     * Create Stripe Checkout session
     */
    private function startStripeCheckout($booking, $type, $amount)
    {
        Stripe::setApiKey(config('stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],

            'line_items' => [[
                'price_data' => [
                    'currency' => 'php',
                    'product_data' => [
                        'name' => strtoupper($type) . ' BOOKING PAYMENT',
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],

            'mode' => 'payment',

            'success_url' => url('/payment/success'),
            'cancel_url'  => url('/payment/cancel'),

            'metadata' => [
                'booking_type'      => $type,
                'booking_reference' => $booking->reference,
            ],

            'payment_intent_data' => [
                'metadata' => [
                    'booking_type'      => $type,
                    'booking_reference' => $booking->reference,
                ],
            ],
        ]);

        return redirect($session->url);
    }

    /**
     * Determine the booking type based on reference prefix
     */
    private function detectBookingType($reference)
    {
        return str_starts_with($reference, 'RB-') ? 'room' : 'service';
    }
}
