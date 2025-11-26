<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Notifications\NewRoomBookingNotification;

class RoomBookingController extends Controller
{
    // View all room bookings
    public function index()
    {
        $bookings = RoomBooking::with(['room', 'user'])
            ->orderBy('booking_date', 'desc')
            ->get();

        $rooms = Room::all(); // or just Room::all();

        return view('RoomBooking.index', compact('bookings', 'rooms'));
    }

    // calendar filter
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
        ]);

        $checkIn = $request->check_in_date;
        $checkOut = $request->check_out_date;
        $guests = $request->number_of_guests;

        // Get rooms where guest count <= room capacity
        $query = Room::where('capacity', '>=', $guests);

        // Filter out rooms that are already booked during the selected dates
        $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
            $q->where(function ($q2) use ($checkIn, $checkOut) {
                $q2->whereBetween('check_in_date', [$checkIn, $checkOut])
                ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                ->orWhere(function ($q3) use ($checkIn, $checkOut) {
                        $q3->where('check_in_date', '<=', $checkIn)
                        ->where('check_out_date', '>=', $checkOut);
                });
            });
        });

        $availableRooms = $query->get();

        if ($availableRooms->count() == 0) {
            return redirect()->back()->with('error', 'No rooms available for selected dates.');
        }

        // Store selected values in session for the next page
        session([
            'availableRooms' => $availableRooms,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'number_of_guests' => $guests
        ]);

        return redirect()
            ->route('room_booking.create')
            ->with('success', 'Rooms available! Please complete the booking form.');
    }


    // Show create page
    public function viewCreatePage()
    {
        // Check if filtered rooms were passed from checkAvailability()
        $availableRooms = session('availableRooms');

        if ($availableRooms) {
            // Use the filtered available rooms
            $rooms = $availableRooms;
        } else {
            // Fallback: default to active + available rooms if accessed directly
            $rooms = Room::active()->available()->get();
        }

        return view('RoomBooking.showCreate', compact('rooms'));
    }

    // Create a booking
    public function create(Request $request)
    {
        $data = $request->validate([
            // Guest Details
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:255',

            // Booking Details
            'room_id'          => 'required|exists:rooms,id',

            'number_of_guests' => 'nullable|integer|min:1',
            'check_in_date'    => 'nullable|date',
            'check_out_date'   => 'nullable|date|after_or_equal:check_in_date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => 'nullable|in:Website,Walk-in,Phone,E-mail',
            'booking_date'     => 'required|date',

            'payment_status'   => 'nullable|in:Unpaid,Partially_Paid,Paid,Refunded',
        ]);

        $data['user_id'] = auth()->id();

        // Create unique booking reference
        $data['reference'] = 'RB-' . strtoupper(Str::random(8));

        $data['booking_status'] = 'Pending'; //default status after creation

        // Save booking
        $booking = RoomBooking::create($data);

        
        // Clear old session availability
        session()->forget('availableRooms');

        // Notify admins
        foreach (User::all() as $admin) {
            $admin->notify(new NewRoomBookingNotification($booking));
        }

        return redirect()
            ->route('room_booking.index_page')
            ->with('success', 'Room booking created successfully.');
    }

    // Update booking
    public function update(Request $request, RoomBooking $booking)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:255',

            'check_in_date'    => 'required|date',
            'check_out_date'   => 'required|date|after_or_equal:check_in_date',

            'remarks'          => 'nullable|string|max:2000',
            'status_change_reason' => 'nullable|string',

            'booking_status' => 'required|in:Pending,Confirmed,Declined,Checked_In,Checked_Out,Cancelled',
            'payment_status' => 'required|in:Unpaid,Partially_Paid,Paid,Refunded',
        ]);
        

        $booking->update($data);


        if ($data['booking_status'] === 'Checked_In') {
            $booking->room->update(['status' => 'Occupied']);
        }

        if ($data['booking_status'] === 'Checked_Out' || $data['booking_status'] === 'Cancelled') {
            $booking->room->update(['status' => 'Available']);
        }
        
        return redirect()->back()->with('success', 'Booking updated.');
    }

    // Delete booking
    public function destroy(RoomBooking $booking)
    {
        $booking->delete();
        return back()->with('success', 'Booking deleted.');
    }
}
