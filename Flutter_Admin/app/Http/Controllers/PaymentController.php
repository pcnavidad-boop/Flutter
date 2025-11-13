<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // View all payments
    public function index()
    {
        $payments = Payment::with(['roomBooking', 'serviceBooking', 'admin'])
            ->orderBy('date', 'desc')
            ->get();

        return view('payment.index', compact('payments'));
    }

    // Create a payment
    public function create(Request $request)
    {
        $data = $request->validate([
            'room_booking_id'    => 'nullable|exists:room_bookings,id',
            'service_booking_id' => 'nullable|exists:service_bookings,id',
            'amount'             => 'required|numeric|min:0',
            'date'               => 'required|date',
            'method'             => 'required|in:Cash,Card,Bank Transfer,E-Wallet',
            'status'             => 'required|in:Pending,Completed,Failed,Refunded',
        ]);

        // Only one booking type may be filled
        if (!$data['room_booking_id'] && !$data['service_booking_id']) {
            return back()->withErrors('You must select either a room or service booking.');
        }

        if ($data['room_booking_id'] && $data['service_booking_id']) {
            return back()->withErrors('A payment cannot belong to both room and service bookings.');
        }

        $data['admin_id'] = auth()->id();

        $payment = Payment::create($data);

        // Update payment status on booking
        if ($payment->room_booking_id) {
            $booking = RoomBooking::find($payment->room_booking_id);
            $booking->update(['payment_status' => $payment->status === 'Completed' ? 'Paid' : 'Unpaid']);
        }

        if ($payment->service_booking_id) {
            $booking = ServiceBooking::find($payment->service_booking_id);
            $booking->update(['payment_status' => $payment->status === 'Completed' ? 'Paid' : 'Unpaid']);
        }

        return redirect()->route('payment.index_page')->with('success', 'Payment recorded.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment deleted.');
    }
}
