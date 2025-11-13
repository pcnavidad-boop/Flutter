<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomBookingController extends Controller
{
    // View all room bookings
    public function index()
    {
        $bookings = RoomBooking::with('room')->orderBy('booking_date', 'desc')->get();

        return view('room_booking.index', compact('bookings'));
    }

    // Show create page
    public function createPage()
    {
        $rooms = Room::active()->available()->get();
        return view('room_booking.create', compact('rooms'));
    }

    // Create a booking
    public function create(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:255',
            'room_id'          => 'required|exists:rooms,id',
            'number_of_guests' => 'required|integer|min:1',

            // dates
            'check_in_date'  => 'nullable|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
            'event_date'     => 'nullable|date',
            'start_time'     => 'nullable|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'         => 'nullable|string|max:2000',
            'type'            => 'required|in:Website,Walk-in,Phone,E-mail',
            'booking_date'    => 'required|date',

            'booking_status'  => 'required|in:Pending,Confirmed,Declined,Checked_In,Checked_Out,Cancelled',
            'payment_status'  => 'required|in:Unpaid,Partially_Paid,Paid,Refunded',
        ]);

        $data['user_id'] = auth()->id();

        RoomBooking::create($data);

        // 🔔 Send notification to admins
        foreach (User::all() as $admin) {
            $admin->notify(new NewRoomBookingNotification($booking));
        }

        return redirect()->route('room_booking.index_page')->with('success', 'Room booking created successfully.');
    }

    // Update booking
    public function update(Request $request, RoomBooking $booking)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:255',
            'room_id'          => 'required|exists:rooms,id',
            'number_of_guests' => 'required|integer|min:1',

            'check_in_date'  => 'nullable|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
            'event_date'     => 'nullable|date',
            'start_time'     => 'nullable|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'         => 'nullable|string|max:2000',
            'booking_status'  => 'required|in:Pending,Confirmed,Declined,Checked_In,Checked_Out,Cancelled',
            'payment_status'  => 'required|in:Unpaid,Partially_Paid,Paid,Refunded',
        ]);

        $booking->update($data);

        return redirect()->back()->with('success', 'Booking updated.');
    }

    // Delete booking
    public function destroy(RoomBooking $booking)
    {
        $booking->delete();
        return back()->with('success', 'Booking deleted.');
    }
}
