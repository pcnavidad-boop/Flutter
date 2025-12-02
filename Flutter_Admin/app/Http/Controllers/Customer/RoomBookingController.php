<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Services\BookingCalculator;
use App\Services\ConflictDetectionService;
use App\Services\BookingLifecycleService;

class RoomBookingController extends Controller
{
    protected ConflictDetectionService $conflict;
    protected BookingLifecycleService $lifecycle;

    public function __construct(
        ConflictDetectionService $conflict,
        BookingLifecycleService $lifecycle
    ) {
        $this->conflict = $conflict;
        $this->lifecycle = $lifecycle;
    }

    public function createPage(Request $request)
    {
        $rooms = Room::active()->available()->orderBy('room_number')->get();
        $selectedRoom = $request->room_id ? Room::find($request->room_id) : null;

        return view('customer.bookings.room.create', compact('rooms', 'selectedRoom'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',
            'room_id'          => 'required|exists:rooms,id',
            'number_of_guests' => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'remarks'          => 'nullable|string|max:2000',
        ]);

        $room = Room::findOrFail($data['room_id']);

        try {
            $this->lifecycle->assertItemBookable($room);
        } catch (\Exception $e) {
            return back()->withErrors(['room_id' => $e->getMessage()]);
        }

        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors([
                'number_of_guests' => "This room supports up to {$room->capacity} guests.",
            ]);
        }

        // Conflict Detection
        if ($this->conflict->roomHasConflict($room, $data['start_date'], $data['end_date'])) {
            return back()->withErrors([
                'start_date' => 'This room is already booked during the selected dates.',
            ]);
        }

        // CREATE BOOKING AS PENDING
        $booking = new RoomBooking();
        $booking->fill($data);
        $booking->reference = RoomBooking::generateReference();
        $booking->booking_status = 'pending';
        $booking->payment_status = 'unpaid';
        $booking->type = 'website';
        $booking->created_by = null;
        $booking->save();

        // Compute total
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return redirect()->route('hotel.booking.room.summary', $booking->reference)
            ->with('success', 'Booking saved! Please proceed to payment.');
    }

    public function summary($reference)
    {
        $booking = RoomBooking::with('room')
            ->where('reference', $reference)
            ->firstOrFail();

        return view('customer.bookings.room.summary', compact('booking'));
    }
}
