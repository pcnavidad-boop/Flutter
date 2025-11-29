<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BookingCalculator;
use App\Services\BookingLifecycleService;
use App\Services\ConflictDetectionService;

class RoomBookingController extends Controller
{
    protected BookingLifecycleService $lifecycle;
    protected ConflictDetectionService $conflict;

    public function __construct(BookingLifecycleService $lifecycle, ConflictDetectionService $conflict)
    {
        $this->lifecycle = $lifecycle;
        $this->conflict = $conflict;
    }

    // Index
    public function index(Request $request)
    {
        $query = RoomBooking::with('room')->orderBy('booking_date', 'desc');

        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        return view('room_booking.index', [
            'bookings' => $query->get(),
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    // Create page
    public function viewCreatePage()
    {
        $rooms = Room::active()->available()->get();
        return view('room_booking.create', compact('rooms'));
    }

    // Create booking
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

        // Check bookable via lifecycle
        try {
            $this->lifecycle->assertItemBookable($room);
        } catch (\Exception $e) {
            return back()->withErrors(['room_id' => $e->getMessage()])->withInput();
        }

        // Capacity check
        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors(['number_of_guests' => "This room supports up to {$room->capacity} guests."])->withInput();
        }

        // Conflict detection via service
        if ($this->conflict->roomHasConflict($room, $data['start_date'], $data['end_date'])) {
            return back()->withErrors(['start_date' => 'This room is already booked during the selected dates.'])->withInput();
        }

        // Persist booking
        $booking = new RoomBooking();
        $booking->guest_name = $data['guest_name'];
        $booking->guest_email = $data['guest_email'];
        $booking->guest_contact = $data['guest_contact'] ?? null;
        $booking->room_id = $room->id;
        $booking->number_of_guests = $data['number_of_guests'];
        $booking->start_date = $data['start_date'];
        $booking->end_date = $data['end_date'];
        $booking->remarks = $data['remarks'] ?? null;
        $booking->type = $data['type'] ?? 'website';
        $booking->created_by = auth()->id();
        $booking->booking_status = 'confirmed';
        $booking->payment_status = 'downpayment';
        $booking->save();

        // compute total (best-effort)
        try {
            $booking->total_price = BookingCalculator::computeTotal($booking);
            $booking->save();
        } catch (\Throwable $e) {}

        // Notify admins
        User::where('role', 'admin')->each(function ($admin) use ($booking) {
            try { $admin->notify(new \App\Notifications\NewRoomBookingNotification($booking)); } catch (\Throwable $e) {}
        });

        return redirect()->route('room_booking.index_page')->with('success', 'Room booking created successfully.');
    }

    // Update booking (no delete)
    public function update(Request $request, RoomBooking $booking)
    {
        try {
            $this->lifecycle->assertBookingEditable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Prevent edits if payments exist (business rule)
        if ($booking->payments()->exists()) {
            return back()->withErrors(['error' => 'Cannot update this booking because payments already exist.']);
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

        // Ensure item still bookable for changes
        try {
            $this->lifecycle->assertItemBookable($room);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Prevent reassigning room
        if ($request->room_id && $request->room_id != $booking->room_id) {
            return back()->withErrors(['room_id' => 'Cannot reassign this booking to a different room.']);
        }

        // Capacity
        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors(['number_of_guests' => "This room supports up to {$room->capacity} guests."]);
        }

        // Conflict detection ignoring this booking
        if ($this->conflict->roomHasConflict($room, $data['start_date'], $data['end_date'], $booking->id)) {
            return back()->withErrors(['start_date' => 'This room is already booked during the selected dates.']);
        }

        // Apply updates
        $booking->guest_name = $data['guest_name'];
        $booking->guest_email = $data['guest_email'];
        $booking->guest_contact = $data['guest_contact'] ?? $booking->guest_contact;
        $booking->number_of_guests = $data['number_of_guests'];
        $booking->start_date = $data['start_date'];
        $booking->end_date = $data['end_date'];
        $booking->remarks = $data['remarks'] ?? null;
        $booking->type = $data['type'] ?? $booking->type;
        $booking->booking_status = $data['booking_status'];
        $booking->payment_status = $data['payment_status'];
        $booking->status_change_reason = $data['status_change_reason'] ?? null;

        // Recompute total
        try {
            $booking->total_price = BookingCalculator::computeTotal($booking);
        } catch (\Throwable $e) {}

        $booking->save();

        return back()->with('success', 'Room booking updated.');
    }
}
