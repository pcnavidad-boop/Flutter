<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\PaymentService;
use App\Services\BookingLifecycleService;

class StripeCheckoutController extends Controller
{
    protected BookingLifecycleService $lifecycle;

    public function __construct(BookingLifecycleService $lifecycle)
    {
        $this->lifecycle = $lifecycle;
    }

    // Pay required downpayment
    public function payDownpayment(string $reference)
    {
        $type = PaymentService::detectBookingType($reference);
        if (!$type) return back()->withErrors(['error' => 'Invalid booking reference.']);

        $booking = PaymentService::findBookingByReference($type, $reference);
        if (!$booking) return back()->withErrors(['error' => 'Booking not found.']);

        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            return back()->withErrors(['error' => 'Cannot process payment for a completed or cancelled booking.']);
        }

        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors(['error' => 'This booking is already fully paid.']);
        }

        $amount = PaymentService::requiredDownpayment($booking);
        $remaining = PaymentService::remainingBalance($booking);
        $amount = min($amount, $remaining);

        if ($amount <= 0) {
            return back()->withErrors(['error' => 'No amount due for downpayment.']);
        }

        return $this->startStripeCheckout($booking, $type, $amount);
    }

    // Pay full
    public function payFull(string $reference)
    {
        $type = PaymentService::detectBookingType($reference);
        if (!$type) return back()->withErrors(['error' => 'Invalid booking reference.']);

        $booking = PaymentService::findBookingByReference($type, $reference);
        if (!$booking) return back()->withErrors(['error' => 'Booking not found.']);

        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            return back()->withErrors(['error' => 'Cannot process payment for a completed or cancelled booking.']);
        }

        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors(['error' => 'This booking is already fully paid.']);
        }

        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) return back()->withErrors(['error' => 'No remaining balance to pay.']);

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    // Pay remaining
    public function payRemaining(string $reference)
    {
        $type = PaymentService::detectBookingType($reference);
        if (!$type) return back()->withErrors(['error' => 'Invalid booking reference.']);

        $booking = PaymentService::findBookingByReference($type, $reference);
        if (!$booking) return back()->withErrors(['error' => 'Booking not found.']);

        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            return back()->withErrors(['error' => 'Cannot process payment for a completed or cancelled booking.']);
        }

        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) return back()->withErrors(['error' => 'No remaining balance left.']);

        return $this->startStripeCheckout($booking, $type, $remaining);
    }

    // Start Stripe Checkout session
    private function startStripeCheckout($booking, string $type, float $amount)
    {
        if (!config('stripe.secret') || !config('stripe.webhook_secret')) {
            return back()->withErrors(['error' => 'Stripe not configured. Contact administrator.']);
        }

        $remaining = PaymentService::remainingBalance($booking);
        $amount = min($amount, $remaining);

        if ($amount <= 0) return back()->withErrors(['error' => 'Nothing to charge.']);

        $unitAmount = intval(round($amount * 100));

        try {
            Stripe::setApiKey(config('stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'php',
                        'product_data' => ['name' => strtoupper($type) . ' BOOKING PAYMENT'],
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
            return back()->withErrors(['error' => 'Failed to initialize Stripe checkout.']);
        }

        return redirect($session->url);
    }
}
