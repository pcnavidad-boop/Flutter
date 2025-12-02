<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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

    // ----------------------------------------------------------------------
    // PAY DOWNPAYMENT
    // ----------------------------------------------------------------------
    public function payDownpayment(string $reference)
    {
        $booking = $this->getBookingOrFail($reference);
        if (!$booking) return $this->invalidRef();

        // Ensure booking can still be paid
        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Compute amount
        $amount = PaymentService::requiredDownpayment($booking);
        $remaining = PaymentService::remainingBalance($booking);
        $amount = min($amount, $remaining);

        if ($amount <= 0) {
            return back()->withErrors(['error' => 'No amount due for downpayment.']);
        }

        return $this->startStripeCheckout($booking, $amount);
    }

    // ----------------------------------------------------------------------
    // PAY FULL AMOUNT
    // ----------------------------------------------------------------------
    public function payFull(string $reference)
    {
        $booking = $this->getBookingOrFail($reference);
        if (!$booking) return $this->invalidRef();

        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) {
            return back()->withErrors(['error' => 'No remaining balance to pay.']);
        }

        return $this->startStripeCheckout($booking, $remaining);
    }

    // ----------------------------------------------------------------------
    // PAY REMAINING BALANCE
    // ----------------------------------------------------------------------
    public function payRemaining(string $reference)
    {
        $booking = $this->getBookingOrFail($reference);
        if (!$booking) return $this->invalidRef();

        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $remaining = PaymentService::remainingBalance($booking);
        if ($remaining <= 0) {
            return back()->withErrors(['error' => 'No remaining balance left.']);
        }

        return $this->startStripeCheckout($booking, $remaining);
    }

    // ----------------------------------------------------------------------
    // START STRIPE CHECKOUT SESSION
    // ----------------------------------------------------------------------
    private function startStripeCheckout($booking, float $amount)
    {
        if (!config('stripe.secret') || !config('stripe.webhook_secret')) {
            return back()->withErrors(['error' => 'Stripe is not configured.']);
        }

        Stripe::setApiKey(config('stripe.secret'));

        $unitAmount = intval(round($amount * 100));

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],

                'line_items' => [[
                    'price_data' => [
                        'currency'     => 'php',
                        'product_data' => [
                            'name' => "Booking Payment ({$booking->reference})",
                        ],
                        'unit_amount'  => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],

                'mode' => 'payment',

                // After customer pays, they return to summary page
                'success_url' => route('payment.success', [
                    'reference' => $booking->reference
                ]),

                'cancel_url' => route('payment.cancel', [
                    'reference' => $booking->reference
                ]),

                // Webhook uses metadata
                'metadata' => [
                    'booking_reference' => $booking->reference,
                    'booking_type'      => PaymentService::detectBookingType($booking->reference),
                ],
            ]);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Could not initialize Stripe checkout.']);
        }

        return redirect($session->url);
    }

    // ----------------------------------------------------------------------
    // SUCCESS REDIRECT (NO DATABASE CHANGES HERE!)
    // ----------------------------------------------------------------------
    public function success(Request $request)
    {
        $reference = $request->reference;
        $type = PaymentService::detectBookingType($reference);

        // Webhook will finalize payment. We just show the summary page.
        return match ($type) {
            'room' => redirect()
                ->route('hotel.booking.room.summary', $reference)
                ->with('success', 'Payment successful! Processing...'),

            'service' => redirect()
                ->route('hotel.booking.service.summary', $reference)
                ->with('success', 'Payment successful! Processing...'),

            default => $this->invalidRef()
        };
    }

    // ----------------------------------------------------------------------
    // CANCEL REDIRECT
    // ----------------------------------------------------------------------
    public function cancel(Request $request)
    {
        $reference = $request->reference;
        $type = PaymentService::detectBookingType($reference);

        return match ($type) {
            'room' => redirect()
                ->route('hotel.booking.room.summary', $reference)
                ->with('error', 'Payment was cancelled.'),

            'service' => redirect()
                ->route('hotel.booking.service.summary', $reference)
                ->with('error', 'Payment was cancelled.'),

            default => $this->invalidRef()
        };
    }

    // ----------------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------------
    private function getBookingOrFail($reference)
    {
        $type = PaymentService::detectBookingType($reference);
        if (!$type) return null;

        return PaymentService::findBookingByReference($type, $reference);
    }

    private function invalidRef()
    {
        return redirect('/')
            ->withErrors(['error' => 'Invalid booking reference.']);
    }
}
