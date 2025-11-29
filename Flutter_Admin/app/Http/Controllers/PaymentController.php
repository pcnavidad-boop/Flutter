<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;
use App\Services\PaymentRollbackService;
use App\Services\BookingLifecycleService;

class PaymentController extends Controller
{
    protected PaymentRollbackService $rollback;
    protected BookingLifecycleService $lifecycle;

    public function __construct(PaymentRollbackService $rollback, BookingLifecycleService $lifecycle)
    {
        $this->rollback = $rollback;
        $this->lifecycle = $lifecycle;
    }

    // List payments
    public function index()
    {
        $payments = \App\Models\Payment::with(['payable', 'processor'])->orderBy('paid_at', 'desc')->get();
        return view('payment.index', compact('payments'));
    }

    // Record offline payment
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
        if ($data['booking_type'] === 'room' && !PaymentService::isRoomReference($data['booking_reference'])) {
            return back()->withErrors(['booking_reference' => 'Invalid room booking reference. (RB-XXXXXXXX expected)']);
        }
        if ($data['booking_type'] === 'service' && !PaymentService::isServiceReference($data['booking_reference'])) {
            return back()->withErrors(['booking_reference' => 'Invalid service booking reference. (SB-XXXXXXXX expected)']);
        }

        $booking = PaymentService::findBookingByReference($data['booking_type'], $data['booking_reference']);
        if (!$booking) {
            return back()->withErrors(['booking_reference' => 'Booking not found.']);
        }

        // Lifecycle check for payable (archived / maintenance / invalid statuses)
        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);
        if ($data['status'] === 'completed' && $data['amount'] > $remaining) {
            return back()->withErrors(['amount' => "Payment exceeds remaining balance of ₱" . number_format($remaining, 2)]);
        }

        // Prevent adding payments if already fully paid
        if ($booking->payment_status === 'fully_paid') {
            return back()->withErrors(['error' => 'This booking is already fully paid.']);
        }

        // Record offline payment
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

        return redirect()->route('payment.index_page')->with('success', 'Offline payment recorded successfully.');
    }

    // Rollback offline payment (no destructive deletion of API/online payments)
    public function destroy(Payment $payment)
    {
        // Prevent rolling back API/online payments here
        if ($payment->method === 'api' || $payment->channel === 'online') {
            return back()->withErrors(['error' => 'Stripe/API payments cannot be deleted here. Issue a refund instead.']);
        }

        if ($payment->status === 'refunded') {
            return back()->withErrors(['error' => 'Refunded payments cannot be deleted.']);
        }

        $booking = $payment->payable;

        try {
            $this->lifecycle->assertBookingMutableForPaymentRollback($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        try {
            // rollbackOfflinePayment will perform transactional safety checks
            $this->rollback->rollbackOfflinePayment($payment, Auth::id());
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to rollback payment: ' . $e->getMessage()]);
        }

        // Recompute booking payment status
        PaymentService::updateBookingPaymentStatus($booking);

        return back()->with('success', 'Payment rolled back and booking updated.');
    }
}
