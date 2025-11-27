<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\PaymentService;

class StripeCheckoutController extends Controller
{
    // Pay required downpayment via Stripe Checkout.
    public function payDownpayment(string $reference)
    {
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return back()->withErrors(['error' => 'Invalid booking reference.']);
        }

        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return back()->withErrors(['error' => 'Booking not found.']);
        }

        // Business rules: no payments on archived/maintenance items
        if (method_exists($booking->payable, 'is_archived') && $booking->payable->is_archived) {
            return back()->withErrors(['error' => 'Cannot pay for a booking belonging to an archived item.']);
        }
        if (method_exists($booking->payable, 'status') && $booking->payable->status === 'maintenance') {
            return back()->withErrors(['error' => 'Cannot pay for a booking while the item is under maintenance.']);
        }

        // Booking status checks
        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            return back()->withErrors(['error' => 'Cannot process payment for a completed or cancelled booking.']);
        }

        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors(['error' => 'This booking is already fully paid.']);
        }

        // Calculate downpayment, clamp to remaining
        $amount = PaymentService::requiredDownpayment($booking);
        $remaining = PaymentService::remainingBalance($booking);
        $amount = min($amount, $remaining);

        if ($amount <= 0) {
            return back()->withErrors(['error' => 'No amount due for downpayment.']);
        }

        return $this->startStripeCheckout($booking, $type, $amount);
    }

    // Pay full booking amount via Stripe Checkout.
    public function payFull(string $reference)
    {
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return back()->withErrors(['error' => 'Invalid booking reference.']);
        }

        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return back()->withErrors(['error' => 'Booking not found.']);
        }

        if (method_exists($booking->payable, 'is_archived') && $booking->payable->is_archived) {
            return back()->withErrors(['error' => 'Cannot pay for a booking belonging to an archived item.']);
        }
        if (method_exists($booking->payable, 'status') && $booking->payable->status === 'maintenance') {
            return back()->withErrors(['error' => 'Cannot pay for a booking while the item is under maintenance.']);
        }

        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            return back()->withErrors(['error' => 'Cannot process payment for a completed or cancelled booking.']);
        }

        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors(['error' => 'This booking is already fully paid.']);
        }

        $remaining = PaymentService::remainingBalance($booking);

        if ($remaining <= 0) {
            return back()->withErrors(['error' => 'No remaining balance to pay.']);
        }

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    // Pay remaining balance via Stripe Checkout.
    public function payRemaining(string $reference)
    {
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return back()->withErrors(['error' => 'Invalid booking reference.']);
        }

        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return back()->withErrors(['error' => 'Booking not found.']);
        }

        if (method_exists($booking->payable, 'is_archived') && $booking->payable->is_archived) {
            return back()->withErrors(['error' => 'Cannot pay for a booking belonging to an archived item.']);
        }
        if (method_exists($booking->payable, 'status') && $booking->payable->status === 'maintenance') {
            return back()->withErrors(['error' => 'Cannot pay for a booking while the item is under maintenance.']);
        }

        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            return back()->withErrors(['error' => 'Cannot process payment for a completed or cancelled booking.']);
        }

        $remaining = PaymentService::remainingBalance($booking);

        if ($remaining <= 0) {
            return back()->withErrors(['error' => 'No remaining balance left.']);
        }

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    /**
     * Start Stripe Checkout session and redirect user.
     *
     * @param  mixed  $booking
     * @param  string $type    // 'room' | 'service'
     * @param  float  $amount  // decimal PHP amount (e.g. 1234.56)
     */
    private function startStripeCheckout($booking, string $type, float $amount)
    {
        // Basic safety checks
        if (!config('stripe.secret') || !config('stripe.webhook_secret')) {
            return back()->withErrors(['error' => 'Stripe not configured. Contact administrator.']);
        }

        // Ensure we never attempt to charge more than remaining
        $remaining = PaymentService::remainingBalance($booking);
        $amount = min($amount, $remaining);

        if ($amount <= 0) {
            return back()->withErrors(['error' => 'Nothing to charge.']);
        }

        // Stripe expects amount in the smallest currency unit (centavos)
        $unitAmount = intval(round($amount * 100));

        try {
            Stripe::setApiKey(config('stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],

                'line_items' => [[
                    'price_data' => [
                        'currency' => 'php',
                        'product_data' => [
                            'name' => strtoupper($type) . ' BOOKING PAYMENT',
                        ],
                        'unit_amount' => $unitAmount,
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
        } catch (\Throwable $e) {
            // Log this in your app logs if you wish (not shown here)
            return back()->withErrors(['error' => 'Failed to initialize Stripe checkout.']);
        }

        return redirect($session->url);
    }
}
