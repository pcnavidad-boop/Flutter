<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\BookingCalculator;
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

    public function __construct(
        PaymentRollbackService $rollback,
        BookingLifecycleService $lifecycle
    ) {
        $this->rollback  = $rollback;
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
                'status'    => $payment->payable->booking_status,
            ],
        ]);
    }

    /**
     * STORE PAYMENT
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_reference' => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'method'            => 'required|in:cash,card,bank_transfer,e_wallet,api',
            'status'            => 'required|in:completed,refunded',
        ]);

        /**
         * DETECT BOOKING TYPE
         */
        $type = PaymentService::detectBookingType($data['booking_reference']);

        if (!$type) {
            return back()
                ->withErrors(['booking_reference' => 'Invalid booking reference format.'])
                ->withInput()
                ->with('open_add_payment_modal', true);
        }

        /**
         * FIND BOOKING
         */
        $booking = PaymentService::findBookingByReference(
            $type,
            $data['booking_reference']
        );

        if (!$booking) {
            return back()
                ->withErrors(['booking_reference' => 'Booking not found.'])
                ->withInput()
                ->with('open_add_payment_modal', true);
        }

        /**
         * 🔒 AUTO-LOCK FINALIZED BOOKINGS
         */
        if (in_array(
            $booking->booking_status,
            ['completed', 'checked_out', 'cancelled']
        )) {
            return back()
                ->withErrors(['error' => 'Payments are locked for finalized bookings.'])
                ->with('open_add_payment_modal', true);
        }

        /**
         * LIFECYCLE SAFETY CHECK
         */
        try {
            $this->lifecycle->assertBookingPayable($booking);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->with('open_add_payment_modal', true);
        }

        /**
         * PREVENT OVERPAYMENT
         */
        $remaining = PaymentService::remainingBalance($booking);

        if (
            $data['status'] === 'completed' &&
            $data['amount'] > $remaining
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'Payment exceeds remaining balance of ₱' .
                        number_format($remaining, 2)
                ])
                ->withInput()
                ->with('open_add_payment_modal', true);
        }

        /**
         * PREVENT OVER-REFUNDING (OPTION 1)
         */
        if ($data['status'] === 'refunded') {

            $completed = (float) $booking->totalPaymentsCompleted();
            $refunded  = (float) $booking->totalPaymentsRefunded();

            $refundableRemaining = max(0, $completed - $refunded);

            if ($refundableRemaining <= 0) {
                return back()
                    ->withErrors(['amount' => 'No refundable balance remaining.'])
                    ->withInput()
                    ->with('open_add_payment_modal', true);
            }

            if ($data['amount'] > $refundableRemaining) {
                return back()
                    ->withErrors([
                        'amount' =>
                            'Refund exceeds refundable amount of ₱' .
                            number_format($refundableRemaining, 2)
                    ])
                    ->withInput()
                    ->with('open_add_payment_modal', true);
            }
        }

        $wasPending = $booking->booking_status === 'pending';

        /**
         * CREATE PAYMENT (LEDGER ENTRY)
         */
        $booking->payments()->create([
            'processed_by' => Auth::id(),
            'amount'       => $data['amount'],
            'method'       => $data['method'],
            'status'       => $data['status'],
            'channel'      => 'offline',
            'paid_at'      => now(),
        ]);

        /**
         * UPDATE BOOKING PAYMENT STATUS
         */
        PaymentService::updateBookingPaymentStatus($booking);

        /**
         * AUTO-CONFIRM BOOKING
         */
        if ($wasPending && $booking->payment_status !== 'unpaid') {
            $booking->booking_status = 'confirmed';
            $booking->save();

            try {
                $booking->notify(new BookingConfirmedNotification($booking));
            } catch (\Throwable $e) {}

            User::all()->each(function ($admin) use ($booking) {
                try {
                    $admin->notify(
                        new BookingBecameConfirmedAdminNotification($booking)
                    );
                } catch (\Throwable $e) {}
            });
        }

        /**
         * PAYMENT RECEIPT
         */
        try {
            $booking->notify(
                new PaymentReceiptNotification($booking, $data['amount'])
            );
        } catch (\Throwable $e) {}

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * DELETE / ROLLBACK PAYMENT
     */
    public function destroy(Payment $payment)
    {
        if ($payment->method === 'api' || $payment->channel === 'online') {
            return back()->withErrors([
                'error' => 'Online/API payments cannot be deleted.'
            ]);
        }

        if ($payment->status === 'refunded') {
            return back()->withErrors([
                'error' => 'Refunded payments cannot be deleted.'
            ]);
        }

        $booking = $payment->payable;

        try {
            $this->lifecycle
                ->assertBookingMutableForPaymentRollback($booking);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }

        try {
            $this->rollback->rollback($payment);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' =>
                    'Failed to rollback payment: ' . $e->getMessage()
            ]);
        }

        PaymentService::updateBookingPaymentStatus($booking);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rolled back successfully.');
    }

    /**
     * AJAX — LIST OPEN BOOKINGS
     */
    public function listBookings(string $type)
    {
        return PaymentService::listOpenBookings($type);
    }

    /**
     * AJAX — BOOKING PAYMENT SUMMARY
     */
    public function bookingSummary(string $type, string $reference)
    {
        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            abort(404);
        }

        $paid     = (float) $booking->totalPaymentsCompleted();
        $refunded = (float) $booking->totalPaymentsRefunded();

        return [
            'total'     => BookingCalculator::computeTotal($booking),
            'paid'      => max(0, $paid - $refunded),
            'remaining' => PaymentService::remainingBalance($booking),
        ];
    }
}
