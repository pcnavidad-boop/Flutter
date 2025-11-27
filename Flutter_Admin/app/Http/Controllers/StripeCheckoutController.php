<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\PaymentService;
use App\Services\BookingCalculator;

class StripeCheckoutController extends Controller
{
    // Pay downpayment
    public function payDownpayment($reference)
    {
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return back()->with('error', 'Invalid booking reference.');
        }

        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        if ($booking->payment_status === 'fully_paid') {
            return back()->with('error', 'This booking is already fully paid.');
        }

        $amount = PaymentService::requiredDownpayment($booking);

        // Prevent paying more than remaining balance
        $remaining = PaymentService::remainingBalance($booking);
        $amount = min($amount, $remaining);

        return $this->startStripeCheckout($booking, $type, $amount);
    }

    // Pay full amount
    public function payFull($reference)
    {
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return back()->with('error', 'Invalid booking reference.');
        }

        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        if ($booking->payment_status === 'fully_paid') {
            return back()->with('error', 'This booking is already fully paid.');
        }

        $remaining = PaymentService::remainingBalance($booking);

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    // Pay remaining balance
    public function payRemaining($reference)
    {
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return back()->with('error', 'Invalid booking reference.');
        }

        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        $remaining = PaymentService::remainingBalance($booking);

        if ($remaining <= 0) {
            return back()->with('error', 'No remaining balance left.');
        }

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    // Start Stripe Checkout Session
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
                    'unit_amount' => intval($amount * 100),
                ],
                'quantity' => 1,
            ]],

            'mode' => 'payment',

            'success_url' => url("/payment/success?reference={$booking->reference}"),
            'cancel_url'  => url("/payment/cancel?reference={$booking->reference}"),

            'metadata' => [
                'booking_type'      => $type,
                'booking_reference' => $booking->reference,
            ],
        ]);

        return redirect($session->url);
    }
}
