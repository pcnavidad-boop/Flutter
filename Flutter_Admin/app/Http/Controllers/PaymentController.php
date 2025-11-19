<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['payable', 'user'])
            ->orderBy('date', 'desc')
            ->get();

        return view('payment.index', compact('payments'));
    }

    // Create a payment
    public function create(Request $request)
    {
        $data = $request->validate([
            'booking_type' => 'required|in:room,service',
            'booking_id'   => 'required|integer',
            'amount'       => 'required|numeric|min:0',
            'date'         => 'required|date',
            'method'       => 'required|in:Cash,Card,Bank Transfer,E-Wallet',
            'status'       => 'required|in:Pending,Completed,Failed,Refunded',
        ]);

        $data['user_id'] = auth()->id();

        $payable = $data['booking_type'] === 'room'
            ? RoomBooking::findOrFail($data['booking_id'])
            : ServiceBooking::findOrFail($data['booking_id']);

        $payment = $payable->payments()->create([
            'user_id' => $data['user_id'],
            'amount'  => $data['amount'],
            'date'    => $data['date'],
            'method'  => $data['method'],
            'status'  => $data['status'],
        ]);

        // Update booking payment status
        $payable->update([
            'payment_status' => $data['status'] === 'Completed' ? 'Paid' : 'Unpaid',
        ]);

        return redirect()->route('payment.index_page')
            ->with('success', 'Payment recorded successfully.');
    }

    // Delete a payment
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment deleted.');
    }
}
