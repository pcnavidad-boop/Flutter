<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BookingCalculator;
use App\Services\BookingLifecycleService;
use App\Services\ConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RoomBookingController extends Controller
{
    protected BookingLifecycleService $lifecycle;
    protected ConflictDetectionService $conflict;

    public function __construct(BookingLifecycleService $lifecycle, ConflictDetectionService $conflict)
    {
        $this->lifecycle = $lifecycle;
        $this->conflict = $conflict;
    }

    // List all room bookings
    public function index(Request $request)
    {
        $query = RoomBooking::with('room')
            ->orderBy('booking_date', 'desc');

        // Filter: Reference (exact match)
        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        // Filter: Booking Status
        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        // Filter: Payment Status
        if ($request->filled('payment')) {
            $query->where('payment_status', $request->payment);
        }

        return view('admin.room_bookings.index', [
            'bookings' => $query->get(),
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    // Show room booking details
    public function show(RoomBooking $booking)
    {
        return response()->json([
            'reference'      => $booking->reference,
            'guest_name'     => $booking->guest_name,
            'guest_email'    => $booking->guest_email,
            'guest_contact'  => $booking->guest_contact,
            'number_of_guests' => $booking->number_of_guests,

            'room' => [
                'id'   => $booking->room->id,
                'name' => $booking->room->name,
                'number' => $booking->room->room_number,
            ],

            'start_date'       => $booking->start_date->toDateString(),
            'end_date'         => $booking->end_date->toDateString(),

            'booking_status'   => $booking->booking_status,
            'payment_status'   => $booking->payment_status,

            'total_price'      => $booking->total_price,
            'remarks'          => $booking->remarks,

            'created_at'       => $booking->created_at->format('M d, Y h:i A'),
            'created_by'       => optional($booking->creator)->name ?? 'System',
        ]);
    }

    // Create a new room booking
    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',
            'room_id'          => 'required|exists:rooms,id',
            'number_of_guests' => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $room = Room::findOrFail($data['room_id']);

        try {
            $this->lifecycle->assertItemBookable($room);
        } catch (\Exception $e) {
            return back()->withErrors(['room_id' => $e->getMessage()])->withInput();
        }

        if ($data['number_of_guests'] > ($room->capacity ?? PHP_INT_MAX)) {
            return back()->withErrors([
                'number_of_guests' => "This room supports up to {$room->capacity} guests."
            ])->withInput();
        }

        if ($this->conflict->roomHasConflict($room, $data['start_date'], $data['end_date'])) {
            return back()->withErrors(['start_date' => 'This room is already booked for these dates.'])->withInput();
        }

        // Create booking (total_price will be computed)
        $booking = RoomBooking::create([
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'],
            'guest_contact'    => $data['guest_contact'] ?? null,
            'room_id'          => $room->id,
            'number_of_guests' => $data['number_of_guests'],
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'remarks'          => $data['remarks'] ?? null,
            'type'             => $data['type'] ?? 'website',
            'created_by'       => auth()->id(),
            'booking_status'   => 'pending',
            'payment_status'   => 'unpaid',
        ]);

        // Compute total with safe-guard
        try {
            $booking->load('room');
            $booking->total_price = BookingCalculator::computeTotal($booking);
            $booking->save();
        } catch (\Throwable $e) {
            // If computation fails, log and delete the newly created booking to avoid bad state
            Log::error('Failed to compute/save booking total after create', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
            ]);

            // Option A: delete booking and return error
            $booking->delete();

            return back()
                ->withErrors(['error' => 'Unable to compute booking total. Please try again or contact admin.'])
                ->withInput();
        }

        return redirect()->route('admin.room_bookings.index')
            ->with('success', 'Booking created as pending. Please record payment to confirm.');
    }

    // Update an existing room booking
    public function update(Request $request, RoomBooking $booking)
    {
        try {
            $this->lifecycle->assertBookingEditable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($booking->payments()->exists()) {
            return back()->withErrors(['error' => 'Cannot update this booking because payments exist.']);
        }

        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',
            'number_of_guests' => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $room = $booking->room;

        if ($data['number_of_guests'] > ($room->capacity ?? PHP_INT_MAX)) {
            return back()->withErrors([
                'number_of_guests' => "This room supports up to {$room->capacity} guests."
            ])->withInput();
        }

        if ($this->conflict->roomHasConflict($room, $data['start_date'], $data['end_date'], $booking->id)) {
            return back()->withErrors(['start_date' => 'This room is already booked for these dates.'])->withInput();
        }

        $booking->update([
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'],
            'guest_contact'    => $data['guest_contact'] ?? null,
            'number_of_guests' => $data['number_of_guests'],
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'remarks'          => $data['remarks'] ?? null,
            'type'             => $data['type'] ?? $booking->type,
        ]);

        // Recompute total safely
        try {
            $booking->load('room');
            $booking->total_price = BookingCalculator::computeTotal($booking);
            $booking->save();
        } catch (\Throwable $e) {
            Log::error('Failed to recompute/save booking total after update', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
            ]);

            return back()->withErrors(['error' => 'Unable to compute booking total. Please try again or contact admin.']);
        }

        return back()->with('success', 'Room booking updated.');
    }

    // Cancel a room booking (Admin only)
    public function cancel(RoomBooking $booking)
    {
        try {
            $this->lifecycle->assertStatusTransition($booking, 'cancelled');
            $this->lifecycle->assertCancelable($booking);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }

        $booking->booking_status = 'cancelled';
        $booking->save();

        return back()->with('success', 'Booking cancelled successfully.');
    }

    // Check room availability for a given month
    public function checkAvailability(Request $request, Room $room)
    {
        $month = $request->query('month'); // YYYY-MM
        $ignoreId = $request->query('ignore'); // booking id to exclude (edit mode)

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['booked_days' => []]);
        }

        [$year, $mon] = explode('-', $month);

        // Determine start & end of the month
        $startOfMonth = "{$month}-01";
        $endOfMonth   = date("Y-m-t", strtotime($startOfMonth)); // last day of month

        $query = RoomBooking::where('room_id', $room->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $bookings = $query->get();

        $bookedDays = [];

        foreach ($bookings as $b) {
            $cur = new \DateTime($b->start_date);
            $end = new \DateTime($b->end_date);

            while ($cur <= $end) {
                $iso = $cur->format('Y-m-d');

                // Only include days inside requested month
                if (strpos($iso, $month) === 0) {
                    $bookedDays[$iso] = true;
                }

                $cur->modify('+1 day');
            }
        }

        return response()->json([
            'booked_days' => $bookedDays,
        ]);
    }
}
