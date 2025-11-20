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

    // Create an offline payment (admin-side only)
    public function create(Request $request)
    {
        $data = $request->validate([
            'booking_type' => 'required|in:room,service',
            'booking_id'   => 'required|integer',
            'amount'       => 'required|numeric|min:0',
            'date'         => 'required|date',
            'method'       => 'required|in:cash,card,bank_transfer,e_wallet',
            'channel'      => 'required|in:offline',
            'status'       => 'required|in:completed,refunded',
            'reference'    => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->id(); // admin performing offline payment

        // Identify booking
        $booking = PaymentService::findBooking($data['booking_type'], $data['booking_id']);

        // Create payment
        $booking->payments()->create([
            'user_id'   => $data['user_id'],
            'reference' => $data['reference'],
            'amount'    => $data['amount'],
            'date'      => $data['date'],
            'method'    => $data['method'],  // cash/card/bank/e_wallet
            'channel'   => 'offline',
            'status'    => $data['status'],
        ]);

        // Recompute booking payment state
        PaymentService::updateBookingPaymentStatus($booking);

        return redirect()
            ->route('payment.index_page')
            ->with('success', 'Offline payment recorded and booking updated.');
    }

    // Delete an offline payment
    public function destroy(Payment $payment)
    {
        $booking = $payment->payable;

        $payment->delete();

        // Recompute booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        return back()->with('success', 'Payment deleted and booking updated.');
    }
}
