<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;

class ServiceBookingController extends Controller
{
    // View service bookings
    public function index()
    {
        $bookings = ServiceBooking::with('service')->orderBy('booking_date', 'desc')->get();
        return view('service_booking.index', compact('bookings'));
    }

    public function createPage()
    {
        $services = Service::active()->available()->get();
        return view('service_booking.create', compact('services'));
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:255',

            'service_id'       => 'required|exists:services,id',

            'date'             => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',
            'number_of_guests' => 'required|integer|min:1',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => 'required|in:Website,Walk-in,Phone,E-mail',
            'booking_date'     => 'required|date',

            'booking_status'   => 'required|in:Pending,Confirmed,Declined,Cancelled,Completed',
            'payment_status'   => 'required|in:Unpaid,Partially_Paid,Paid,Refunded',
        ]);

        $data['user_id'] = auth()->id();

        ServiceBooking::create($data);

        // 🔔 Notify admins
        foreach (User::all() as $admin) {
            $admin->notify(new NewServiceBookingNotification($booking));
        }

        return redirect()->route('service_booking.index_page')->with('success', 'Service booking created.');
    }

    public function update(Request $request, ServiceBooking $booking)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:255',
            'date'             => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',
            'number_of_guests' => 'required|integer|min:1',

            'remarks'          => 'nullable|string|max:2000',
            'booking_status'   => 'required|in:Pending,Confirmed,Declined,Cancelled,Completed',
            'payment_status'   => 'required|in:Unpaid,Partially_Paid,Paid,Refunded',
        ]);

        $booking->update($data);

        return back()->with('success', 'Service booking updated.');
    }

    public function destroy(ServiceBooking $booking)
    {
        $booking->delete();
        return back()->with('success', 'Booking deleted.');
    }
}
