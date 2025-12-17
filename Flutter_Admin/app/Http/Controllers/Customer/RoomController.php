<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\ConflictDetectionService;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::where('is_archived', false)
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('customer.rooms.index', compact('rooms'));
    }

    public function show(Room $room)
    {
        if ($room->is_archived || $room->status !== 'available') {
            return redirect()
                ->route('hotel.rooms')
                ->withErrors(['error' => 'This room is no longer available.']);
        }

        return view('customer.rooms.show', compact('room'));
    }

    /**
     * Availability endpoint (used by Room Detail page only)
     */
    public function availability(Request $request, ConflictDetectionService $conflict)
    {
        /**
         * STEP 1: Allow flexible input
         * - date_range OR start_date + end_date
         */
        $request->validate([
            'date_range' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'guests'     => 'required|integer|min:1',
            'room_id'    => 'nullable|exists:rooms,id',
        ]);

        /**
         * STEP 2: Normalize date_range → start_date / end_date
         */
        if (
            $request->filled('date_range') &&
            (!$request->filled('start_date') || !$request->filled('end_date'))
        ) {
            [$start, $end] = array_map(
                'trim',
                explode('to', $request->date_range)
            );

            $request->merge([
                'start_date' => $start,
                'end_date'   => $end,
            ]);
        }

        /**
         * STEP 3: Determine room type (function vs stay)
         */
        $room = $request->room_id
            ? Room::find($request->room_id)
            : null;

        /**
         * STEP 4: Strict validation after normalization
         */
        $rules = [
            'start_date' => 'required|date|after_or_equal:today',
            'guests'     => 'required|integer|min:1',
        ];

        // Function rooms: same-day allowed
        if ($room && $room->room_type === 'function') {
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }
        // Stay rooms: minimum 1-night stay
        else {
            $rules['end_date'] = 'required|date|after:start_date';
        }

        $data = $request->validate($rules);

        /**
         * STEP 5: Filter available rooms
         */
        $rooms = Room::active()
            ->available()
            ->where('capacity', '>=', $data['guests'])
            ->get()
            ->filter(function ($room) use ($conflict, $data) {
                return !$conflict->roomHasConflict(
                    $room,
                    $data['start_date'],
                    $data['end_date']
                );
            })
            ->values();

        return response()->json(
            $rooms->map(fn ($room) => [
                'id'         => $room->id,
                'name'       => $room->name,
                'base_price' => number_format($room->base_price, 2),
            ])
        );
    }

    /**
     * Booked date ranges for Flatpickr
     * - Stay rooms: checkout day allowed
     * - Function rooms: full-day inclusive
     */
    public function bookedDates(Room $room)
    {
        $ranges = $room->bookings()
            ->where('booking_status', 'confirmed')
            ->get(['start_date', 'end_date'])
            ->map(function ($booking) use ($room) {

                // Function rooms: block full range
                if ($room->room_type === 'function') {
                    return [
                        'from' => $booking->start_date->format('Y-m-d'),
                        'to'   => $booking->end_date->format('Y-m-d'),
                    ];
                }

                // Stay rooms: allow checkout day
                return [
                    'from' => $booking->start_date->format('Y-m-d'),
                    'to'   => Carbon::parse($booking->end_date)
                                ->subDay()
                                ->format('Y-m-d'),
                ];
            })
            ->filter(fn ($range) => $range['from'] <= $range['to'])
            ->values();

        return response()->json($ranges);
    }
}
