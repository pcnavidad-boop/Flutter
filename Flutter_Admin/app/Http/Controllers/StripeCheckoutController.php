<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeCheckoutController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'booking_type' => 'required|in:room,service',
            'booking_id'   => 'required|integer',
            'amount'       => 'required|numeric|min:1',
        ]);

        Stripe::setApiKey(config('stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'php',
                    'product_data' => [
                        'name' => strtoupper($data['booking_type']) . ' BOOKING PAYMENT',
                    ],
                    'unit_amount' => $data['amount'] * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',

            'success_url' => url('/payment/success'),
            'cancel_url'  => url('/payment/cancel'),

            'metadata' => [
                'booking_type' => $data['booking_type'],
                'booking_id'   => $data['booking_id'],
            ],

            // IMPORTANT: include PaymentIntent so webhook receives full details
            'payment_intent_data' => [
                'metadata' => [
                    'booking_type' => $data['booking_type'],
                    'booking_id'   => $data['booking_id'],
                ],
            ],
        ]);

        return redirect($session->url);
    }
}
