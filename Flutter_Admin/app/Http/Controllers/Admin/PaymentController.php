<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;
use App\Services\PaymentRollbackService;
use App\Services\BookingLifecycleService;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\PaymentReceiptNotification;
use App\Notifications\BookingBecameConfirmedAdminNotification;
use App\Models\User;

class PaymentController extends Controller
{
    protected PaymentRollbackService $rollback;
    protected BookingLifecycleService $lifecycle;

    public function __construct(PaymentRollbackService $rollback, BookingLifecycleService $lifecycle)
    {
        $this->rollback = $rollback;
        $this->lifecycle = $lifecycle;
    }

    public function index()
    {
        $payments = Payment::with(['payable', 'processor'])
            ->orderBy('paid_at', 'desc')
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        return response()->json([
            'id'        => $payment->id,
            'reference' => $payment->reference,
            'amount'    => $payment->amount,
            'method'    => $payment->method,
            'channel'   => $payment->channel,
            'status'    => $payment->status,
            'paid_at'   => $payment->paid_at->format('M d, Y h:i A'),
            'processor' => optional($payment->processor)->name,
            'booking'   => [
                'reference' => $payment->payable->reference,
                'type'      => class_basename($payment->payable),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_type'      => 'required|in:room,service',
            'booking_reference' => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'method'            => 'required|in:cash,card,bank_transfer,e_wallet,api',
            'status'            => 'required|in:completed,refunded',
        ]);

        // Validate reference format
        if ($data['booking_type'] === 'room' && !PaymentService::isRoomReference($data['booking_reference'])) {
            return back()->withErrors(['booking_reference' => 'Invalid room reference.'])->withInput();
        }
        if ($data['booking_type'] === 'service' && !PaymentService::isServiceReference($data['booking_reference'])) {
            return back()->withErrors(['booking_reference' => 'Invalid service reference.'])->withInput();
        }

        // Find booking
        $booking = PaymentService::findBookingByReference($data['booking_type'], $data['booking_reference']);
        if (!$booking) {
            return back()->withErrors(['booking_reference' => 'Booking not found.'])->withInput();
        }

        // Check lifecycle
        try {
            $this->lifecycle->assertBookingEditableForPayment($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Prevent overpayment
        $remaining = PaymentService::remainingBalance($booking);
        if ($data['status'] === 'completed' && $data['amount'] > $remaining) {
            return back()->withErrors([
                'amount' => "Payment exceeds remaining balance of ₱" . number_format($remaining, 2)
            ]);
        }

        $wasPending = $booking->booking_status === 'pending';

        // Create payment
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

        // If booking was pending → now confirmed
        if ($wasPending && $booking->payment_status !== 'unpaid') {

            $booking->booking_status = 'confirmed';
            $booking->save();

            // Send guest booking confirmation
            try {
                $booking->notify(new BookingConfirmedNotification($booking));
            } catch (\Throwable $e) {}

            // Send admin notification
            User::where('role', 'admin')->each(function ($admin) use ($booking) {
                try {
                    $admin->notify(new BookingBecameConfirmedAdminNotification($booking));
                } catch (\Throwable $e) {}
            });
        }

        // Always send payment receipt
        try {
            $booking->notify(new PaymentReceiptNotification($booking, $data['amount']));
        } catch (\Throwable $e) {}

        return redirect()->route('admin.payments.index')
            ->with('success', 'Offline payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->method === 'api' || $payment->channel === 'online') {
            return back()->withErrors(['error' => 'Online/API payments cannot be deleted.']);
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
            $this->rollback->rollback($payment);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to rollback payment: ' . $e->getMessage()]);
        }

        PaymentService::updateBookingPaymentStatus($booking);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment rolled back and booking updated.');
    }
}
