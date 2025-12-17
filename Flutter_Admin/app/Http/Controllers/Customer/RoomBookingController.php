<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RoomBookingController extends Controller
{
    /**
     * Show booking form (Step 1)
     */
    public function createPage(Room $room, Request $request)
    {
        // Guard: must come from availability flow
        if (
            !$request->start_date ||
            !$request->end_date ||
            !$request->guests
        ) {
            return redirect()
                ->route('hotel.room.show', $room)
                ->with('error', 'Please select dates and guests first.');
        }

        return view('customer.bookings.room.create', [
            'room' => $room,
        ]);
    }

    /**
     * Store booking (POST)
     */
    public function store(Request $request)
    {
        // ---------------------------
        // VALIDATION
        // ---------------------------
        $data = $request->validate([
            'room_id'          => 'required|exists:rooms,id',
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:1000',
        ]);

        $room = Room::findOrFail($data['room_id']);

        // ---------------------------
        // CAPACITY CHECK (admin parity)
        // ---------------------------
        if ($data['number_of_guests'] > ($room->capacity ?? PHP_INT_MAX)) {
            return back()
                ->withErrors([
                    'number_of_guests' =>
                        "This room supports up to {$room->capacity} guests."
                ])
                ->withInput();
        }

        // ---------------------------
        // DATE NORMALIZATION
        // ---------------------------
        $start  = Carbon::parse($data['start_date']);
        $end    = Carbon::parse($data['end_date']);
        $nights = max($start->diffInDays($end), 1);

        // ---------------------------
        // AVAILABILITY CHECK (authoritative)
        // ---------------------------
        $hasConflict = $room->bookings()
            ->where('booking_status', 'confirmed')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q) use ($start, $end) {
                      $q->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                  });
            })
            ->exists();

        if ($hasConflict) {
            return back()
                ->withErrors([
                    'start_date' =>
                        'This room is no longer available for the selected dates.'
                ])
                ->withInput();
        }

        // ---------------------------
        // CREATE BOOKING (safe)
        // ---------------------------
        try {
            $total = $nights * $room->base_price;

            $booking = RoomBooking::create([
                'reference'        => strtoupper(Str::random(10)),
                'room_id'          => $room->id,
                'guest_name'       => $data['guest_name'],
                'guest_email'      => $data['guest_email'],
                'guest_contact'    => $data['guest_contact'],
                'start_date'       => $start,
                'end_date'         => $end,
                'number_of_guests' => $data['number_of_guests'],
                'total_price'      => $total,
                'booking_status'   => 'confirmed',
                'remarks'          => $data['remarks'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Customer booking failed', [
                'error'   => $e->getMessage(),
                'room_id'=> $room->id,
            ]);

            return back()
                ->withErrors([
                    'error' =>
                        'Unable to complete booking. Please try again.'
                ])
                ->withInput();
        }

        // ---------------------------
        // SUCCESS → SUMMARY
        // ---------------------------
        return redirect()->route(
            'hotel.book.room.summary',
            $booking->reference
        );
    }

    /**
     * Booking summary (Step 2)
     */
    public function summary(string $reference)
    {
        $booking = RoomBooking::where('reference', $reference)
            ->with('room')
            ->firstOrFail();

        return view('customer.bookings.room.summary', [
            'booking' => $booking,
        ]);
    }

    /**
     * Landing-page availability endpoint
     * Used by "Stay With Us" picker
     */
    public function availableRooms(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'guests'     => 'required|integer|min:1',
        ]);

        $start  = Carbon::parse($request->start_date);
        $end    = Carbon::parse($request->end_date);
        $guests = $request->guests;

        $rooms = Room::where('is_archived', false)
            ->where('status', 'available')
            ->where('capacity', '>=', $guests)
            ->get()
            ->filter(function ($room) use ($start, $end) {
                return !$room->bookings()
                    ->where('booking_status', 'confirmed')
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('start_date', [$start, $end])
                          ->orWhereBetween('end_date', [$start, $end])
                          ->orWhere(function ($q) use ($start, $end) {
                              $q->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                          });
                    })
                    ->exists();
            })
            ->values()
            ->map(fn ($room) => [
                'id'    => $room->id,
                'name'  => $room->name,
                'price' => (int) $room->base_price,
            ]);

        return response()->json($rooms);
    }
}
