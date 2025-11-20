<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Notifications\NewRoomBookingNotification;
use Illuminate\Validation\Rule;

class RoomBookingController extends Controller
{
    // View all room bookings
    public function index()
    {
        $bookings = RoomBooking::with('room')
            ->orderBy('booking_date', 'desc')
            ->get();

        return view('room_booking.index', compact('bookings'));
    }

    // Show create page
    public function viewCreatePage()
    {
        $rooms = Room::active()->available()->get();
        return view('room_booking.create', compact('rooms'));
    }

    // Create a booking
    public function create(Request $request)
    {
        $data = $request->validate([
            // Guest Details
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',

            // Booking Details
            'room_id'          => 'required|exists:rooms,id',

            'number_of_guests' => 'required|integer|min:1',
            'check_in_date'    => 'nullable|date',
            'check_out_date'   => 'nullable|date|after_or_equal:check_in_date',
            'event_date'       => 'nullable|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['required', Rule::in(['website','walk-in','phone','email'])],
            'booking_date'     => 'required|date',

            'booking_status'   => 'confirmed',
            'payment_status'   => 'required|in:downpayment,fully_paid',
        ]);

        $data['user_id'] = auth()->id();

        // Generate unique reference
        $data['reference'] = 'RB-' . strtoupper(Str::random(8));

        // Save booking
        $booking = RoomBooking::create($data);

        // Notify admins
        foreach (User::where('role', 'admin')->get() as $admin) {
            $admin->notify(new NewRoomBookingNotification($booking));
        }

        return redirect()
            ->route('room_booking.index_page')
            ->with('success', 'Room booking created successfully.');
    }

    // Update a booking
    public function update(Request $request, RoomBooking $booking)
    {
        $data = $request->validate([
            // Guest Details
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',

            // Booking Details
            'room_id'          => 'required|exists:rooms,id',

            'number_of_guests' => 'required|integer|min:1',
            'check_in_date'    => 'nullable|date',
            'check_out_date'   => 'nullable|date|after_or_equal:check_in_date',
            'event_date'       => 'nullable|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['required', Rule::in(['website','walk-in','phone','email'])],
            'booking_date'     => 'required|date',

            'booking_status'   => ['required', Rule::in(['confirmed','checked_in','checked_out','cancelled'])],
            'payment_status'   => ['required', Rule::in(['downpayment','fully_paid','refunded'])],
        ]);

        $booking->update($data);

        return redirect()->back()->with('success', 'Booking updated.');
    }

    // Delete a booking
    public function destroy(RoomBooking $booking)
    {
        $booking->delete();
        return back()->with('success', 'Room booking deleted.');
    }
}
