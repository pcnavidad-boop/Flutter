<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\BookingCalculator;
use Carbon\Carbon;

class RoomBookingController extends Controller
{
    // View all room bookings (with optional reference filtering)
    public function index(Request $request)
    {
        $query = RoomBooking::with('room')->orderBy('booking_date', 'desc');

        // Filter by reference for NotificationController redirects
        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        $bookings = $query->get();

        return view('room_booking.index', [
            'bookings' => $bookings,
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    // View create room booking page
    public function viewCreatePage()
    {
        // Only available, active rooms
        $rooms = Room::active()->available()->get();
        return view('room_booking.create', compact('rooms'));
    }

    // Create a booking
    public function create(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',
            'room_id'          => 'required|exists:rooms,id',
            'number_of_guests' => 'required|integer|min:1',

            // Stay rooms
            'check_in_date'    => 'nullable|date',
            'check_out_date'   => 'nullable|date|after:check_in_date',

            // Function rooms
            'event_start_date' => 'nullable|date',
            'event_end_date'   => 'nullable|date|after_or_equal:event_start_date',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $room = Room::findOrFail($data['room_id']);

        // Enforce guest capacity
        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors([
                'number_of_guests' => "This room only supports up to {$room->capacity} guest(s)."
            ])->withInput();
        }

        //------------------------------------
        // ROOM TYPE LOGIC (Stay vs Function)
        //------------------------------------
        if ($room->room_type === 'function') {

            // Must use event_start_date / event_end_date
            $request->validate([
                'event_start_date' => 'required|date',
                'event_end_date'   => 'required|date|after_or_equal:event_start_date',
            ]);

            $data['check_in_date'] = null;
            $data['check_out_date'] = null;

            // Overlap check for multi-day events
            $conflict = RoomBooking::where('room_id', $room->id)
                ->where('booking_status', '!=', 'cancelled')
                ->where(function($q) use ($data) {
                    $q->whereNotNull('event_start_date')
                      ->whereNotNull('event_end_date')
                      ->where('event_start_date', '<=', $data['event_end_date'])
                      ->where('event_end_date', '>=', $data['event_start_date']);
                })
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'event_start_date' => 'This function room is already booked during the selected event dates.'
                ])->withInput();
            }

        } else {

            // Stay rooms must use check_in/check_out
            $request->validate([
                'check_in_date'  => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
            ]);

            $data['event_start_date'] = null;
            $data['event_end_date'] = null;

            // Overlap check for stay rooms
            $conflict = RoomBooking::where('room_id', $room->id)
                ->where('booking_status', '!=', 'cancelled')
                ->whereNotNull('check_in_date')
                ->whereNotNull('check_out_date')
                ->where(function ($q) use ($data) {
                    $q->where('check_in_date', '<', $data['check_out_date'])
                      ->where('check_out_date', '>', $data['check_in_date']);
                })
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'check_in_date' => 'This room is already booked during the selected stay dates.'
                ])->withInput();
            }
        }

        // Safe to create fields
        $booking = new RoomBooking();
        $booking->guest_name = $data['guest_name'];
        $booking->guest_email = $data['guest_email'];
        $booking->guest_contact = $data['guest_contact'] ?? null;

        $booking->room_id = $room->id;
        $booking->number_of_guests = $data['number_of_guests'];

        $booking->check_in_date = $data['check_in_date'] ?? null;
        $booking->check_out_date = $data['check_out_date'] ?? null;

        $booking->event_start_date = $data['event_start_date'] ?? null;
        $booking->event_end_date = $data['event_end_date'] ?? null;

        $booking->remarks = $data['remarks'] ?? null;
        $booking->type = $data['type'] ?? 'website';

        // System fields
        $booking->user_id = auth()->id();
        $booking->booking_status = 'confirmed';
        $booking->payment_status = 'downpayment';

        $booking->save();

        // Calculate total price
        try {
            $booking->total_price = BookingCalculator::computeTotal($booking);
            $booking->save();
        } catch (\Throwable $e) {
            \Log::error("BookingCalculator failed for room booking {$booking->id}: ".$e->getMessage());
        }

        // Notify admins
        foreach (User::where('role', 'admin')->get() as $admin) {
            try {
                $admin->notify(new \App\Notifications\NewRoomBookingNotification($booking));
            } catch (\Throwable $e) {
                \Log::error("Failed to notify admin for room booking {$booking->id}: ".$e->getMessage());
            }
        }

        return redirect()->route('room_booking.index_page')
            ->with('success', 'Room booking created successfully.');
    }

    // Update a booking
    public function update(Request $request, RoomBooking $booking)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',
            'number_of_guests' => 'required|integer|min:1',

            // Stay rooms
            'check_in_date'    => 'nullable|date',
            'check_out_date'   => 'nullable|date|after:check_in_date',

            // Function rooms
            'event_start_date' => 'nullable|date',
            'event_end_date'   => 'nullable|date|after_or_equal:event_start_date',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
            'booking_status'   => ['required', Rule::in(['confirmed','checked_in','checked_out','cancelled'])],
            'payment_status'   => ['required', Rule::in(['downpayment','fully_paid','refunded'])],
            'status_change_reason' => 'nullable|string|max:2000',
        ]);

        $room = $booking->room;

        // Capacity rules
        if ($data['number_of_guests'] > $room->capacity) {
            return back()->withErrors([
                'number_of_guests' => "This room can only host {$room->capacity} guest(s)."
            ]);
        }

        //-----------------
        // ROOM TYPE LOGIC
        //-----------------
        if ($room->room_type === 'function') {

            $request->validate([
                'event_start_date' => 'required|date',
                'event_end_date'   => 'required|date|after_or_equal:event_start_date',
            ]);

            $data['check_in_date'] = null;
            $data['check_out_date'] = null;

            // Overlap check excluding current booking
            $conflict = RoomBooking::where('room_id', $room->id)
                ->where('id', '!=', $booking->id)
                ->where('booking_status', '!=', 'cancelled')
                ->whereNotNull('event_start_date')
                ->whereNotNull('event_end_date')
                ->where(function ($q) use ($data) {
                    $q->where('event_start_date', '<=', $data['event_end_date'])
                      ->where('event_end_date', '>=', $data['event_start_date']);
                })
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'event_start_date' => 'This function room is already booked during the selected event dates.'
                ]);
            }

        } else {

            $request->validate([
                'check_in_date'  => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
            ]);

            $data['event_start_date'] = null;
            $data['event_end_date'] = null;

            $conflict = RoomBooking::where('room_id', $room->id)
                ->where('id', '!=', $booking->id)
                ->where('booking_status', '!=', 'cancelled')
                ->whereNotNull('check_in_date')
                ->whereNotNull('check_out_date')
                ->where(function ($q) use ($data) {
                    $q->where('check_in_date', '<', $data['check_out_date'])
                      ->where('check_out_date', '>', $data['check_in_date']);
                })
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'check_in_date' => 'This room is already booked during the selected stay dates.'
                ]);
            }
        }

        // Safe to update fields
        $booking->guest_name = $data['guest_name'];
        $booking->guest_email = $data['guest_email'];
        $booking->guest_contact = $data['guest_contact'] ?? $booking->guest_contact;

        $booking->number_of_guests = $data['number_of_guests'];
        $booking->remarks = $data['remarks'] ?? null;

        $booking->check_in_date = $data['check_in_date'] ?? null;
        $booking->check_out_date = $data['check_out_date'] ?? null;

        $booking->event_start_date = $data['event_start_date'] ?? null;
        $booking->event_end_date = $data['event_end_date'] ?? null;

        $booking->type = $data['type'] ?? $booking->type;

        $booking->booking_status = $data['booking_status'];
        $booking->payment_status = $data['payment_status'];
        $booking->status_change_reason = $data['status_change_reason'] ?? null;

        // Recompute total price
        try {
            $booking->total_price = BookingCalculator::computeTotal($booking);
        } catch (\Throwable $e) {
            \Log::error("Total price recompute failed for booking {$booking->id}: ".$e->getMessage());
        }

        $booking->save();

        return back()->with('success', 'Room booking updated.');
    }

    // Delete a booking
    public function destroy(RoomBooking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Room booking deleted.');
    }
}
