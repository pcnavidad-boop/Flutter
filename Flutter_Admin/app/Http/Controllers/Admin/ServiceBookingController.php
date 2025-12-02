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
                'id'   => $booking->service->id,
                'name' => $booking->service->name,
            ],
            'number_of_guests' => $booking->number_of_guests,
            'appointment_date' => $booking->appointment_date->toDateString(),
            'start_time'       => $booking->start_time,
            'end_time'         => $booking->end_time,
            'booking_status'   => $booking->booking_status,
            'payment_status'   => $booking->payment_status,
            'total_price'      => $booking->total_price,
            'remarks'          => $booking->remarks,
            'created_at'       => $booking->created_at->format('M d, Y h:i A'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $service = Service::findOrFail($data['service_id']);

        try {
            $this->lifecycle->assertItemBookable($service);
        } catch (\Exception $e) {
            return back()->withErrors(['service_id' => $e->getMessage()]);
        }

        // Hours validation
        $svcStart = Carbon::parse($service->start_time);
        $svcEnd   = Carbon::parse($service->end_time);
        $reqStart = Carbon::parse($data['start_time']);
        $reqEnd   = Carbon::parse($data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors([
                'start_time' => "Appointment must be within operating hours ({$service->start_time} - {$service->end_time})."
            ]);
        }

        // Guest capacity
        if (in_array($service->service_type, ['restaurant','bar','spa']) &&
            $data['number_of_guests'] > $service->capacity
        ) {
            return back()->withErrors([
                'number_of_guests' => "Maximum {$service->capacity} guests allowed."
            ]);
        }

        // Conflict check
        if ($this->conflict->serviceHasConflict($service, $data)) {
            return back()->withErrors([
                'appointment_date' => "This service is fully booked at that time."
            ]);
        }

        // FINAL: pending + unpaid
        $booking = ServiceBooking::create([
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'],
            'guest_contact'    => $data['guest_contact'] ?? null,
            'service_id'       => $data['service_id'],
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'number_of_guests' => $data['number_of_guests'],
            'remarks'          => $data['remarks'] ?? null,
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
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $service = $booking->service;

        try {
            $this->lifecycle->assertItemBookable($service);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Validate within hours
        $svcStart = Carbon::parse($service->start_time);
        $svcEnd   = Carbon::parse($service->end_time);
        $reqStart = Carbon::parse($data['start_time']);
        $reqEnd   = Carbon::parse($data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors([
                'start_time' => "Appointment must be within operating hours ({$service->start_time} - {$service->end_time})."
            ]);
        }

        // Validate capacity
        if (in_array($service->service_type, ['restaurant','bar','spa']) &&
            $data['number_of_guests'] > $service->capacity
        ) {
            return back()->withErrors([
                'number_of_guests' => "Maximum {$service->capacity} guests allowed."
            ]);
        }

        // Conflict (allow updating own record)
        if ($this->conflict->serviceHasConflict($service, $data, $booking->id)) {
            return back()->withErrors([
                'appointment_date' => "This service is fully booked at that time."
            ]);
        }

        $booking->update([
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'],
            'guest_contact'    => $data['guest_contact'] ?? null,
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'number_of_guests' => $data['number_of_guests'],
            'remarks'          => $data['remarks'] ?? null,
            'type'             => $data['type'] ?? $booking->type,
        ]);

        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return back()->with('success', 'Service booking updated.');
    }
}
