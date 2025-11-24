<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['payable', 'user'])
            ->orderBy('date', 'desc')
            ->get();

        return view('payment.index', compact('payments'));
    }

    // Create an offline payment (admin-side)
    public function create(Request $request)
    {
        $data = $request->validate([
            'booking_type'      => 'required|in:room,service',
            'booking_reference' => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'date'              => 'required|date',
            'method'            => 'required|in:cash,card,bank_transfer,e_wallet',
            'channel'           => 'required|in:offline',
            'status'            => 'required|in:completed,refunded',
            'reference'         => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->id();

        // Find booking by reference
        $booking = PaymentService::findBookingByReference($data['booking_type'], $data['booking_reference']);

        if (!$booking) {
            return back()->withErrors(['booking_reference' => 'Booking not found.']);
        }

        // Compute remaining balance
        $remaining = PaymentService::remainingBalance($booking);

        // Prevent overpayment
        if ($data['amount'] > $remaining) {
            return back()->withErrors([
                'amount' => "This payment exceeds the remaining balance of ₱" . number_format($remaining, 2)
            ]);
        }

        // Create offline payment
        $booking->payments()->create([
            'user_id'   => $data['user_id'],
            'reference' => $data['reference'],
            'amount'    => $data['amount'],
            'date'      => $data['date'],
            'method'    => $data['method'],
            'channel'   => 'offline',
            'status'    => $data['status'],
        ]);

        // Recalculate booking payment state
        PaymentService::updateBookingPaymentStatus($booking);

        return redirect()
            ->route('payment.index_page')
            ->with('success', 'Offline payment recorded and booking updated.');
    }
}
