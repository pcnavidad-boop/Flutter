<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BookingCalculator;

class RoomBookingController extends Controller
{
    // View list of room bookings
    public function index(Request $request)
    {
        $query = RoomBooking::with('room')
            ->orderBy('booking_date', 'desc');

        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        return view('room_booking.index', [
            'bookings'     => $query->get(),
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    // Create page view
    public function viewCreatePage()
    {
        $rooms = Room::active()->available()->get();
        return view('room_booking.create', compact('rooms'));
    }

    // Create a new room booking
    public function create(Request $request)
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
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $room = Room::findOrFail($data['room_id']);

        // ❗ Cannot book archived/maintenance rooms
        if ($room->is_archived) {
            return back()->withErrors(['room_id' => 'Cannot create booking for an archived room.']);
        }
        if ($room->status === 'maintenance') {
            return back()->withErrors(['room_id' => 'This room is under maintenance.']);
        }

        // Capacity check
        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors([
                'number_of_guests' => "This room supports up to {$room->capacity} guests."
            ])->withInput();
        }

        // Check date conflict
        if ($this->roomHasConflict($room, $data['start_date'], $data['end_date'])) {
            return back()->withErrors(['start_date' =>
                'This room is already booked during the selected dates.'
            ])->withInput();
        }

        // Create booking
        $booking = new RoomBooking();

        $booking->guest_name       = $data['guest_name'];
        $booking->guest_email      = $data['guest_email'];
        $booking->guest_contact    = $data['guest_contact'] ?? null;

        $booking->room_id          = $room->id;
        $booking->number_of_guests = $data['number_of_guests'];

        $booking->start_date       = $data['start_date'];
        $booking->end_date         = $data['end_date'];

        $booking->remarks          = $data['remarks'] ?? null;

        $booking->type             = $data['type'] ?? 'website';

        // System fields
        $booking->created_by       = auth()->id();
        $booking->booking_status   = 'confirmed';
        $booking->payment_status   = 'downpayment';

        $booking->save();

        // Compute price
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        // Notify admins
        User::where('role', 'admin')->each(function ($admin) use ($booking) {
            try { $admin->notify(new \App\Notifications\NewRoomBookingNotification($booking)); }
            catch (\Throwable $e) {}
        });

        return redirect()->route('room_booking.index_page')
            ->with('success', 'Room booking created successfully.');
    }

    // Update a room booking
    public function update(Request $request, RoomBooking $booking)
    {
        // ❗ Cannot update cancelled / checked-out
        if (in_array($booking->booking_status, ['cancelled', 'checked_out'])) {
            return back()->withErrors(['error' => 'You cannot update cancelled or checked-out bookings.']);
        }

        // ❗ Cannot update if payments already exist
        if ($booking->payments()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot update this booking because payments already exist.'
            ]);
        }

        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',

            'number_of_guests' => 'required|integer|min:1',

            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',

            'remarks'          => 'nullable|string|max:2000',

            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
            'booking_status'   => ['required', Rule::in(['confirmed','checked_in','checked_out','cancelled'])],
            'payment_status'   => ['required', Rule::in(['downpayment','fully_paid','refunded'])],
            'status_change_reason' => 'nullable|string|max:2000',
        ]);

        $room = $booking->room;

        // ❗ Cannot update if room is archived/maintenance
        if ($room->is_archived) {
            return back()->withErrors(['error' => 'Cannot update booking of an archived room.']);
        }
        if ($room->status === 'maintenance') {
            return back()->withErrors(['error' => 'Cannot update booking while room is under maintenance.']);
        }

        // Prevent changing room_id
        if ($request->room_id && $request->room_id != $booking->room_id) {
            return back()->withErrors([
                'room_id' => 'Cannot reassign this booking to a different room.'
            ]);
        }

        // Capacity check
        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors([
                'number_of_guests' => "This room supports up to {$room->capacity} guests."
            ]);
        }

        // Conflict detection
        if ($this->roomHasConflict($room, $data['start_date'], $data['end_date'], $booking->id)) {
            return back()->withErrors(['start_date' =>
                'This room is already booked during the selected dates.'
            ]);
        }

        // Update fields
        $booking->guest_name       = $data['guest_name'];
        $booking->guest_email      = $data['guest_email'];
        $booking->guest_contact    = $data['guest_contact'] ?? $booking->guest_contact;

        $booking->number_of_guests = $data['number_of_guests'];
        $booking->start_date       = $data['start_date'];
        $booking->end_date         = $data['end_date'];

        $booking->remarks          = $data['remarks'] ?? null;

        $booking->type                 = $data['type'] ?? $booking->type;
        $booking->booking_status       = $data['booking_status'];
        $booking->payment_status       = $data['payment_status'];
        $booking->status_change_reason = $data['status_change_reason'] ?? null;

        // Recompute total
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return back()->with('success', 'Room booking updated.');
    }

    // Delete a room booking
    public function destroy(RoomBooking $booking)
    {
        // Cannot delete cancelled/checked-out
        if (in_array($booking->booking_status, ['checked_out','cancelled'])) {
            return back()->withErrors([
                'error' => 'Cannot delete checked-out or cancelled bookings.'
            ]);
        }

        // Cannot delete if payments exist
        if ($booking->payments()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete booking because payments exist.'
            ]);
        }

        // ❗ Cannot delete if room is archived
        if ($booking->room->is_archived) {
            return back()->withErrors([
                'error' => 'Cannot delete booking for an archived room.'
            ]);
        }

        $booking->delete();

        return back()->with('success', 'Room booking deleted.');
    }

    // Conflict detection logic
    private function roomHasConflict(Room $room, string $start, string $end, $ignoreId = null): bool
    {
        $q = RoomBooking::where('room_id', $room->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start);

        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }
}
