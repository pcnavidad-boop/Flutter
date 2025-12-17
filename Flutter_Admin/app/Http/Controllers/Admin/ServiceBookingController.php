<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BookingCalculator;
use App\Services\BookingLifecycleService;
use App\Services\ConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ServiceBookingController extends Controller
{
    protected BookingLifecycleService $lifecycle;
    protected ConflictDetectionService $conflict;

    public function __construct(BookingLifecycleService $lifecycle, ConflictDetectionService $conflict)
    {
        $this->lifecycle = $lifecycle;
        $this->conflict = $conflict;
    }

    public function index(Request $request)
    {
        $query = ServiceBooking::with('service')->orderBy('booking_date', 'desc');

        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        if ($request->filled('payment')) {
            $query->where('payment_status', $request->payment);
        }

        return view('admin.service_bookings.index', [
            'bookings' => $query->get(),
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    public function show(ServiceBooking $booking)
    {
        return response()->json([
            'reference'      => $booking->reference,
            'guest_name'     => $booking->guest_name,
            'guest_email'    => $booking->guest_email,
            'guest_contact'  => $booking->guest_contact,
            'service' => [
                'id'        => $booking->service->id,
                'name'      => $booking->service->name,
                'location'  => $booking->service->location,
                'type'      => $booking->service->service_type,
                'capacity'  => $booking->service->capacity ?? null,
            ],

            'number_of_guests' => $booking->number_of_guests,
            'appointment_date' => $booking->appointment_date?->toDateString(),
            'start_time'       => $booking->start_time,
            'end_time'         => $booking->end_time,

            'booking_status'   => $booking->booking_status,
            'payment_status'   => $booking->payment_status,

            'total_price'      => $booking->total_price,
            'remarks'          => $booking->remarks,

            'created_at'       => $booking->created_at->format('M d, Y h:i A'),
            'created_by'       => optional($booking->creator)->name ?? 'System',
        ]);
    }

    // Create a new service booking
    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],

            // NOTE: start_time / end_time not required here — validated after we know service type
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
        ]);

        $service = Service::findOrFail($data['service_id']);

        // Determine if service is time-based
        $isTimeBased = in_array($service->service_type, ['spa', 'restaurant', 'bar']);

        if ($isTimeBased) {
            // Must have valid times
            if (!$request->start_time) {
                return back()->withErrors(['start_time' => 'Please select a start time.'])->withInput();
            }

            $start = Carbon::parse($request->start_time);
            $end   = (clone $start)->addHour(); // ENFORCE 1-hour duration

            // Validate within operating hours
            $svcStart = Carbon::parse($service->start_time);
            $svcEnd   = Carbon::parse($service->end_time);

            if ($start->lt($svcStart) || $end->gt($svcEnd)) {
                return back()->withErrors([
                    'start_time' => "Time must be within operating hours ({$service->start_time} - {$service->end_time})."
                ])->withInput();
            }

            // Attach generated end_time
            $data['start_time'] = $start->format('H:i');
            $data['end_time']   = $end->format('H:i');
        } else {
            // Non-time-based services ignore time
            $data['start_time'] = null;
            $data['end_time']   = null;
        }

        // Guest capacity rule
        if ($isTimeBased && $data['number_of_guests'] > $service->capacity) {
            return back()->withErrors([
                'number_of_guests' => "Maximum {$service->capacity} guests allowed."
            ])->withInput();
        }

        // Conflict check
        if ($this->conflict->serviceHasConflict($service, $data)) {
            return back()->withErrors([
                'start_time' => "This time slot is already fully booked."
            ])->withInput();
        }

        $booking = ServiceBooking::create([
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'],
            'guest_contact'    => $data['guest_contact'],
            'service_id'       => $service->id,
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'number_of_guests' => $data['number_of_guests'],
            'remarks'          => $data['remarks'],
            'type'             => $data['type'] ?? 'website',
            'created_by'       => auth()->id(),
            'booking_status'   => 'pending',
            'payment_status'   => 'unpaid',
        ]);

        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return redirect()->route('admin.service_bookings.index')
            ->with('success', 'Service booking created as pending. Please record payment to confirm.');
    }

    // Update an existing service booking
    public function update(Request $request, ServiceBooking $booking)
    {
        try {
            $this->lifecycle->assertBookingEditable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        if ($booking->payments()->exists()) {
            return back()->withErrors(['error' => 'Cannot update: payments exist.']);
        }

        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',
            'appointment_date' => 'required|date',
            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],

            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
        ]);

        $service = $booking->service;

        $isTimeBased = in_array($service->service_type, ['spa', 'restaurant', 'bar']);

        if ($isTimeBased) {
            if (!$request->start_time) {
                return back()->withErrors(['start_time' => 'Please select a start time.']);
            }

            $start = Carbon::parse($request->start_time);
            $end   = (clone $start)->addHour();

            $svcStart = Carbon::parse($service->start_time);
            $svcEnd   = Carbon::parse($service->end_time);

            if ($start->lt($svcStart) || $end->gt($svcEnd)) {
                return back()->withErrors([
                    'start_time' => "Time must be within operating hours ({$service->start_time} - {$service->end_time})."
                ]);
            }

            $data['start_time'] = $start->format('H:i');
            $data['end_time']   = $end->format('H:i');
        } else {
            $data['start_time'] = null;
            $data['end_time']   = null;
        }

        if ($isTimeBased && $data['number_of_guests'] > $service->capacity) {
            return back()->withErrors([
                'number_of_guests' => "Maximum {$service->capacity} guests allowed."
            ]);
        }

        if ($this->conflict->serviceHasConflict($service, $data, $booking->id)) {
            return back()->withErrors([
                'start_time' => "This time slot is already fully booked."
            ]);
        }

        $booking->update([
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'],
            'guest_contact'    => $data['guest_contact'],
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'number_of_guests' => $data['number_of_guests'],
            'remarks'          => $data['remarks'],
            'type'             => $data['type'] ?? $booking->type,
        ]);

        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return back()->with('success', 'Service booking updated successfully.');
    }

    // Cancel a service booking (Admin only)
    public function cancel(ServiceBooking $booking)
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

        return back()->with('success', 'Service booking cancelled successfully.');
    }

    public function checkAvailability(Service $service, Request $request)
    {
        $month = $request->query('month'); // "YYYY-MM"

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['full' => []]);
        }

        $first = $month . "-01";
        $last  = date("Y-m-t", strtotime($first));

        $bookings = ServiceBooking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereBetween('appointment_date', [$first, $last])
            ->get()
            ->groupBy('appointment_date');

        $full = [];

        foreach ($bookings as $date => $items) {
            // For day-capacity (non-time-based), compare sum of guests or number of bookings depending on service type
            $sumGuests = $items->sum('number_of_guests');

            if ($service->capacity && $sumGuests >= $service->capacity) {
                $full[$date] = true;
                continue;
            }

            // For time-based services we want to mark days where every slot is full — conservative: if any slot reaches capacity mark the day full
            // We'll compute midday by grouping by times
            if (in_array($service->service_type, ['spa','restaurant','bar'])) {
                // try to detect if there exists any time interval that is fully booked — if yes mark day full
                $times = [];
                foreach ($items as $b) {
                    if ($b->start_time && $b->end_time) {
                        $times[] = ['start' => $b->start_time, 'end' => $b->end_time, 'guests' => $b->number_of_guests];
                    }
                }

                // naive check: for each booking interval, compute concurrent guests at that interval
                foreach ($times as $i => $t) {
                    $concurrent = 0;
                    foreach ($times as $j => $o) {
                        if ($o['start'] < $t['end'] && $o['end'] > $t['start']) {
                            $concurrent += $o['guests'];
                        }
                    }
                    if ($service->capacity && $concurrent >= $service->capacity) {
                        $full[$date] = true;
                        break;
                    }
                }
            }
        }

        return response()->json([
            'full' => $full
        ]);
    }
}
