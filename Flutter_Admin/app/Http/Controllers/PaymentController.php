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
        $payments = Payment::with(['payable', 'processor'])
            ->orderBy('paid_at', 'desc')
            ->get();

        return view('payment.index', compact('payments'));
    }

    // Create an offline payment
    public function create(Request $request)
    {
        $data = $request->validate([
            'booking_type'      => 'required|in:room,service',
            'booking_reference' => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'method'            => 'required|in:cash,card,bank_transfer,e_wallet',
            'status'            => 'required|in:completed,refunded',
        ]);

        // Reference validation
        if ($data['booking_type'] === 'room' &&
            !PaymentService::isRoomReference($data['booking_reference'])) {
            return back()->withErrors([
                'booking_reference' =>
                    'Invalid room booking reference. (RB-XXXXXXXX expected)'
            ]);
        }

        if ($data['booking_type'] === 'service' &&
            !PaymentService::isServiceReference($data['booking_reference'])) {
            return back()->withErrors([
                'booking_reference' =>
                    'Invalid service booking reference. (SB-XXXXXXXX expected)'
            ]);
        }

        // Locate booking
        $booking = PaymentService::findBookingByReference(
            $data['booking_type'],
            $data['booking_reference']
        );

        if (!$booking) {
            return back()->withErrors([
                'booking_reference' => 'Booking not found.'
            ]);
        }

        // Prevent payments for archived services/rooms
        if (method_exists($booking->payable, 'is_archived') &&
            $booking->payable->is_archived) {
            return back()->withErrors([
                'error' => 'Cannot pay for a booking belonging to an archived item.'
            ]);
        }

        // Prevent payments on invalid booking statuses
        if (in_array($booking->booking_status, [
            'cancelled', 'checked_out', 'completed'
        ])) {
            return back()->withErrors([
                'error' =>
                    'Cannot process payment for a completed or cancelled booking.'
            ]);
        }

        // Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);

        if ($data['status'] === 'completed' && $data['amount'] > $remaining) {
            return back()->withErrors([
                'amount' =>
                    "Payment exceeds remaining balance of ₱" .
                    number_format($remaining, 2)
            ]);
        }

        // Prevent adding payments if already fully paid
        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors([
                'error' => 'This booking is already fully paid.'
            ]);
        }

        // Record payment
        $payment = $booking->payments()->create([
            'processed_by' => Auth::id(),
            'amount'       => $data['amount'],
            'method'       => $data['method'],
            'status'       => $data['status'],
            'channel'      => 'offline',
            'paid_at'      => now(),
        ]);

        // Update booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        return redirect()
            ->route('payment.index_page')
            ->with('success', 'Offline payment recorded successfully.');
    }

    // Delete a payment
    public function destroy(Payment $payment)
    {
        $booking = $payment->payable;

        // Rule: Cannot delete API stripe payments
        if ($payment->method === 'api') {
            return back()->withErrors([
                'error' => 'Stripe (API) payments cannot be deleted.'
            ]);
        }

        // Rule: Cannot delete refunded payments
        if ($payment->status === 'refunded') {
            return back()->withErrors([
                'error' => 'Refunded payments cannot be deleted.'
            ]);
        }

        // Rule: Cannot delete payments for completed/cancelled bookings
        if (in_array($booking->booking_status, [
            'cancelled', 'checked_out', 'completed'
        ])) {
            return back()->withErrors([
                'error' => 'Cannot delete payments for completed or cancelled bookings.'
            ]);
        }

        // Rule: Cannot delete if booking item is archived
        if (method_exists($booking->payable, 'is_archived') &&
            $booking->payable->is_archived) {
            return back()->withErrors([
                'error' => 'Cannot delete payments for archived rooms/services.'
            ]);
        }

        // Rule: Cannot delete if it would cause negative balance
        $remainingAfterDelete =
            PaymentService::remainingBalance($booking) + $payment->amount;

        if ($remainingAfterDelete < 0) {
            return back()->withErrors([
                'error' =>
                    'This payment cannot be deleted because it breaks balance consistency.'
            ]);
        }

        // All good — delete payment
        $payment->delete();

        // Recompute booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        return back()->with('success', 'Payment deleted and booking updated.');
    }
}
