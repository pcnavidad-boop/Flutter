<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    // View all payments
    public function index()
    {
        $payments = Payment::with(['payable', 'user'])
            ->orderBy('date', 'desc')
            ->get();

        return view('payment.index', compact('payments'));
    }

    // Create offline payment
    public function create(Request $request)
    {
        $data = $request->validate([
            'booking_type'      => 'required|in:room,service',
            'booking_reference' => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'method'            => 'required|in:cash,card,bank_transfer,e_wallet',
            'status'            => 'required|in:completed,refunded',
        ]);

        // Validate reference format
        if ($data['booking_type'] === 'room' &&
            !PaymentService::isRoomReference($data['booking_reference'])) {

            return back()->withErrors([
                'booking_reference' => 'Invalid room reference format (RB-XXXXXXXX expected).'
            ]);
        }

        if ($data['booking_type'] === 'service' &&
            !PaymentService::isServiceReference($data['booking_reference'])) {

            return back()->withErrors([
                'booking_reference' => 'Invalid service reference format (SB-XXXXXXXX expected).'
            ]);
        }

        // Find booking by reference
        $booking = PaymentService::findBookingByReference(
            $data['booking_type'],
            $data['booking_reference']
        );

        if (!$booking) {
            return back()->withErrors([
                'booking_reference' => 'Booking not found.'
            ]);
        }

        // Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);

        if ($data['amount'] > $remaining && $data['status'] === 'completed') {
            return back()->withErrors([
                'amount' => "This payment exceeds the remaining balance of ₱" . number_format($remaining, 2)
            ]);
        }

        // Record payment
        $payment = $booking->payments()->create([
            'user_id'   => Auth::id(),
            'amount'    => $data['amount'],
            'method'    => $data['method'],
            'status'    => $data['status'],
            'channel'   => 'offline',
            'date'      => now(),
        ]);

        PaymentService::updateBookingPaymentStatus($booking);

        return redirect()
            ->route('payment.index_page')
            ->with('success', 'Offline payment recorded successfully.');
    }

    // Delete a payment
    public function destroy(Payment $payment)
    {
        $booking = $payment->payable;

        // Rule 1: Cannot delete online/API payments
        if ($payment->method === 'api') {
            return back()->withErrors([
                'error' => 'Online (API) payments cannot be deleted. Issue a Stripe refund instead.'
            ]);
        }

        // Rule 2: Cannot delete payments for finalized bookings
        if (in_array($booking->booking_status, ['checked_out', 'cancelled'])) {
            return back()->withErrors([
                'error' => 'Payments for completed or cancelled bookings cannot be deleted.'
            ]);
        }

        // Rule 3: Cannot delete refunded payments
        if ($payment->status === 'refunded') {
            return back()->withErrors([
                'error' => 'Refunded payments cannot be deleted.'
            ]);
        }

        // Rule 4: Cannot delete if booking is fully paid
        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors([
                'error' => 'Cannot delete this payment because the booking is fully paid.'
            ]);
        }

        // PASS — delete payment
        $payment->delete();
        PaymentService::updateBookingPaymentStatus($booking);

        return back()->with('success', 'Payment deleted and booking updated.');
    }
}
