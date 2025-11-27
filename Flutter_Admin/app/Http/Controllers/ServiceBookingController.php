<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BookingCalculator;
use Carbon\Carbon;

class ServiceBookingController extends Controller
{
    // View list of service bookings
    public function index(Request $request)
    {
        $query = ServiceBooking::with('service')->orderBy('booking_date', 'desc');

        // Allow NotificationController to redirect using ?ref=
        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        $bookings = $query->get();

        return view('service_booking.index', [
            'bookings'     => $bookings,
            'highlightRef' => $request->ref ?? null,
        ]);
    }

    // Create page view
    public function viewCreatePage()
    {
        $services = Service::active()->available()->get();
        return view('service_booking.create', compact('services'));
    }

    // Create a new service booking
    public function create(Request $request)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',

            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date',

            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',

            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $svcType = $service->service_type;

        // Operating hours validation
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ]);

        if (!($service->start_time && $service->end_time)) {
            return back()->withErrors([
                'start_time' => "This service has no operating hours configured."
            ]);
        }

        // Ensure requested times fall within operating hours
        $svcStart = Carbon::createFromFormat('H:i', $service->start_time);
        $svcEnd   = Carbon::createFromFormat('H:i', $service->end_time);
        $reqStart = Carbon::createFromFormat('H:i', $data['start_time']);
        $reqEnd   = Carbon::createFromFormat('H:i', $data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors([
                'start_time' =>
                    "Appointment must be within operating hours ({$service->start_time} - {$service->end_time})."
            ])->withInput();
        }

        // Capacity rule
        $capacityRequired = in_array($svcType, ['restaurant','bar','spa']);

        if ($capacityRequired) {
            $request->validate([
                'number_of_guests' => "required|integer|min:1|max:{$service->capacity}"
            ]);
        }

        // Conflict check
        if ($this->hasConflict($service, $data)) {
            return back()->withErrors([
                'appointment_date' =>
                    "This service is already fully booked at that time."
            ])->withInput();
        }

        // Create booking
        $booking = new ServiceBooking();
        $booking->guest_name       = $data['guest_name'];
        $booking->guest_email      = $data['guest_email'];
        $booking->guest_contact    = $data['guest_contact'] ?? null;

        $booking->service_id       = $data['service_id'];
        $booking->appointment_date = $data['appointment_date'];
        $booking->start_time       = $data['start_time'];
        $booking->end_time         = $data['end_time'];

        $booking->number_of_guests = $data['number_of_guests'];
        $booking->remarks          = $data['remarks'] ?? null;

        $booking->type = $data['type'] ?? 'website';

        // System fields
        $booking->user_id        = auth()->id();
        $booking->booking_status = 'confirmed';
        $booking->payment_status = 'downpayment';

        $booking->save();

        // Calculate total price
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return redirect()
            ->route('service_booking.index_page')
            ->with('success', 'Service booking created.');
    }

    // Update a service booking
    public function update(Request $request, ServiceBooking $booking)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:50',

            'appointment_date' => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after_or_equal:start_time',

            'number_of_guests' => 'required|integer|min:1',

            'remarks'          => 'nullable|string|max:2000',

            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
            'booking_status'   => ['required', Rule::in(['confirmed','completed','cancelled'])],
            'payment_status'   => ['required', Rule::in(['downpayment','fully_paid','refunded'])],
            'status_change_reason' => 'nullable|string|max:2000',
        ]);

        $service = $booking->service;
        $svcType = $service->service_type;

        // Capacity rule
        if (in_array($svcType, ['restaurant','bar','spa']) &&
            $data['number_of_guests'] > $service->capacity) {

            return back()->withErrors([
                'number_of_guests' =>
                    "Capacity exceeded. Max allowed: {$service->capacity} guest(s)."
            ]);
        }

        // Time falls within service hours
        $svcStart = Carbon::createFromFormat('H:i', $service->start_time);
        $svcEnd   = Carbon::createFromFormat('H:i', $service->end_time);
        $reqStart = Carbon::createFromFormat('H:i', $data['start_time']);
        $reqEnd   = Carbon::createFromFormat('H:i', $data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors([
                'start_time' =>
                    "Appointment must be within operating hours ({$service->start_time} - {$service->end_time})."
            ]);
        }

        // Conflict check (exclude self)
        if ($this->hasConflict($service, $data, $booking->id)) {
            return back()->withErrors([
                'appointment_date' =>
                    "This service is already fully booked at that time."
            ])->withInput();
        }

        // Update safe fields
        $booking->guest_name       = $data['guest_name'];
        $booking->guest_email      = $data['guest_email'];
        $booking->guest_contact    = $data['guest_contact'] ?? null;

        $booking->appointment_date = $data['appointment_date'];
        $booking->start_time       = $data['start_time'];
        $booking->end_time         = $data['end_time'];

        $booking->number_of_guests = $data['number_of_guests'];
        $booking->remarks          = $data['remarks'] ?? null;

        $booking->type               = $data['type'] ?? $booking->type;
        $booking->booking_status     = $data['booking_status'];
        $booking->payment_status     = $data['payment_status'];
        $booking->status_change_reason = $data['status_change_reason'] ?? null;

        $booking->total_price = BookingCalculator::computeTotal($booking);

        $booking->save();

        return back()->with('success', 'Service booking updated.');
    }

    // Delete a service booking
    public function destroy(ServiceBooking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Service booking deleted.');
    }

    // Check for booking conflicts
    private function hasConflict(Service $service, array $data, $ignoreId = null): bool
    {
        $svcType = $service->service_type;

        // Base query
        $base = ServiceBooking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('appointment_date', $data['appointment_date']);

        if ($ignoreId) {
            $base->where('id', '!=', $ignoreId);
        }

        // Time-based capacity (e.g. spa, restaurant, bar)
        if (in_array($svcType, ['spa','restaurant','bar'])) {

            // Count overlapping bookings
            $count = (clone $base)
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->count();

            return $count >= $service->capacity;
        }

        // Date-based capacity (e.g. gym, swimming pool)
        if (in_array($svcType, ['gym','swimming_pool'])) {
            $count = $base->count();
            return $count >= $service->capacity;
        }

        return false; 
    }
}
