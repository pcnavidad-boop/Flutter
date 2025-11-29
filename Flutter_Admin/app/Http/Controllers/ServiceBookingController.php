<?php

namespace App\Http\Controllers;

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

    // Index
    public function index(Request $request)
    {
        $query = ServiceBooking::with('service')->orderBy('booking_date', 'desc');

        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        return view('service_booking.index', [
            'bookings' => $query->get(),
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    // Create page
    public function viewCreatePage()
    {
        $services = Service::active()->available()->get();
        return view('service_booking.create', compact('services'));
    }

    // Create booking
    public function create(Request $request)
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

        // Lifecycle check
        try {
            $this->lifecycle->assertItemBookable($service);
        } catch (\Exception $e) {
            return back()->withErrors(['service_id' => $e->getMessage()])->withInput();
        }

        // Operating hours
        $svcStart = Carbon::createFromFormat('H:i', $service->start_time);
        $svcEnd   = Carbon::createFromFormat('H:i', $service->end_time);
        $reqStart = Carbon::createFromFormat('H:i', $data['start_time']);
        $reqEnd   = Carbon::createFromFormat('H:i', $data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors(['start_time' => "Appointment must be within operating hours ({$service->start_time} - {$service->end_time})."])->withInput();
        }

        // Capacity enforcement for types that require it
        if (in_array($service->service_type, ['restaurant','bar','spa']) && $data['number_of_guests'] > $service->capacity) {
            return back()->withErrors(['number_of_guests' => "Maximum {$service->capacity} guests allowed."])->withInput();
        }

        // Conflict detection
        if ($this->conflict->serviceHasConflict($service, $data)) {
            return back()->withErrors(['appointment_date' => "This service is already fully booked at that time."])->withInput();
        }

        // Persist
        $booking = new ServiceBooking();
        $booking->guest_name = $data['guest_name'];
        $booking->guest_email = $data['guest_email'];
        $booking->guest_contact = $data['guest_contact'] ?? null;
        $booking->service_id = $data['service_id'];
        $booking->appointment_date = $data['appointment_date'];
        $booking->start_time = $data['start_time'];
        $booking->end_time = $data['end_time'];
        $booking->number_of_guests = $data['number_of_guests'];
        $booking->remarks = $data['remarks'] ?? null;
        $booking->type = $data['type'] ?? 'website';
        $booking->created_by = auth()->id();
        $booking->booking_status = 'confirmed';
        $booking->payment_status = 'downpayment';
        $booking->save();

        // compute total
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return redirect()->route('service_booking.index_page')->with('success', 'Service booking created successfully.');
    }

    // Update booking
    public function update(Request $request, ServiceBooking $booking)
    {
        try {
            $this->lifecycle->assertBookingEditable($booking);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Prevent changes if payments exist
        if ($booking->payments()->exists()) {
            return back()->withErrors(['error' => 'Cannot update this booking because payments already exist.']);
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
            'booking_status'   => ['required', Rule::in(['confirmed','completed','cancelled'])],
            'payment_status'   => ['required', Rule::in(['downpayment','fully_paid','refunded'])],
            'status_change_reason' => 'nullable|string|max:2000',
        ]);

        $service = $booking->service;

        // Ensure service still bookable
        try {
            $this->lifecycle->assertItemBookable($service);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Cannot reassign
        if ($request->service_id && $request->service_id != $booking->service_id) {
            return back()->withErrors(['service_id' => 'Cannot reassign this booking to a different service.']);
        }

        // Hours & capacity checks
        $svcStart = Carbon::createFromFormat('H:i', $service->start_time);
        $svcEnd   = Carbon::createFromFormat('H:i', $service->end_time);
        $reqStart = Carbon::createFromFormat('H:i', $data['start_time']);
        $reqEnd   = Carbon::createFromFormat('H:i', $data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors(['start_time' => "Appointment must be within operating hours ({$service->start_time} - {$service->end_time})."]);
        }

        if (in_array($service->service_type, ['restaurant','bar','spa']) && $data['number_of_guests'] > $service->capacity) {
            return back()->withErrors(['number_of_guests' => "Maximum {$service->capacity} guests allowed."]);
        }

        // Conflict check ignoring this booking
        if ($this->conflict->serviceHasConflict($service, $data, $booking->id)) {
            return back()->withErrors(['appointment_date' => "This service is already fully booked at that time."]);
        }

        // Apply updates
        $booking->guest_name = $data['guest_name'];
        $booking->guest_email = $data['guest_email'];
        $booking->guest_contact = $data['guest_contact'] ?? null;
        $booking->appointment_date = $data['appointment_date'];
        $booking->start_time = $data['start_time'];
        $booking->end_time = $data['end_time'];
        $booking->number_of_guests = $data['number_of_guests'];
        $booking->remarks = $data['remarks'] ?? null;
        $booking->type = $data['type'] ?? $booking->type;
        $booking->booking_status = $data['booking_status'];
        $booking->payment_status = $data['payment_status'];
        $booking->status_change_reason = $data['status_change_reason'] ?? null;
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return back()->with('success', 'Service booking updated.');
    }

    // Conflict detection helper (kept as private fallback)
    private function hasConflict(Service $service, array $data, $ignoreId = null): bool
    {
        return $this->conflict->serviceHasConflict($service, $data, $ignoreId);
    }
}
