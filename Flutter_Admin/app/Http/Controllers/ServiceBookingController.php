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
        $query = ServiceBooking::with('service')
            ->orderBy('booking_date', 'desc');

        if ($request->filled('ref')) {
            $query->where('reference', $request->ref);
        }

        return view('service_booking.index', [
            'bookings'     => $query->get(),
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

            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',

            'number_of_guests' => 'required|integer|min:1',
            'remarks'          => 'nullable|string|max:2000',

            'type'             => ['nullable', Rule::in(['website','walk-in','phone','email'])],
        ]);

        $service = Service::findOrFail($data['service_id']);

        // Enforce status + archive rules
        if ($service->is_archived) {
            return back()->withErrors([
                'service_id' => 'Cannot book an archived service.'
            ]);
        }
        if ($service->status === 'maintenance') {
            return back()->withErrors([
                'service_id' => 'This service is under maintenance.'
            ]);
        }

        $svcType = $service->service_type;

        // Operating hours enforcement
        $svcStart = Carbon::createFromFormat('H:i', $service->start_time);
        $svcEnd   = Carbon::createFromFormat('H:i', $service->end_time);
        $reqStart = Carbon::createFromFormat('H:i', $data['start_time']);
        $reqEnd   = Carbon::createFromFormat('H:i', $data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors([
                'start_time' => "Appointment must be within operating hours "
                              . "({$service->start_time} - {$service->end_time})."
            ])->withInput();
        }

        // Capacity enforcement
        if (in_array($svcType, ['restaurant','bar','spa'])) {
            if ($data['number_of_guests'] > $service->capacity) {
                return back()->withErrors([
                    'number_of_guests' =>
                        "Maximum {$service->capacity} guests allowed."
                ])->withInput();
            }
        }

        // Conflict detection
        if ($this->hasConflict($service, $data)) {
            return back()->withErrors([
                'appointment_date' =>
                    "This service is already fully booked at that time."
            ])->withInput();
        }

        // Save booking
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

        $booking->type             = $data['type'] ?? 'website';

        // System fields
        $booking->created_by       = auth()->id();
        $booking->booking_status   = 'confirmed';
        $booking->payment_status   = 'downpayment';

        $booking->save();

        // Compute price
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return redirect()
            ->route('service_booking.index_page')
            ->with('success', 'Service booking created successfully.');
    }

    // Update a service booking
    public function update(Request $request, ServiceBooking $booking)
    {
        // Cannot update completed/cancelled bookings
        if (in_array($booking->booking_status, ['completed','cancelled'])) {
            return back()->withErrors([
                'error' => 'Cannot update completed or cancelled bookings.'
            ]);
        }

        // Prevent updates if payments exist
        if ($booking->payments()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot update this booking because payments already exist.'
            ]);
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

        // Cannot update booking if service is archived/maintenance
        if ($service->is_archived) {
            return back()->withErrors([
                'error' => 'Cannot update a booking of an archived service.'
            ]);
        }
        if ($service->status === 'maintenance') {
            return back()->withErrors([
                'error' => 'Cannot update booking while service is under maintenance.'
            ]);
        }

        // Cannot change service_id
        if ($request->service_id && $request->service_id != $booking->service_id) {
            return back()->withErrors([
                'service_id' => 'Cannot reassign this booking to a different service.'
            ]);
        }

        // Operate hours validation
        $svcStart = Carbon::createFromFormat('H:i', $service->start_time);
        $svcEnd   = Carbon::createFromFormat('H:i', $service->end_time);
        $reqStart = Carbon::createFromFormat('H:i', $data['start_time']);
        $reqEnd   = Carbon::createFromFormat('H:i', $data['end_time']);

        if ($reqStart->lt($svcStart) || $reqEnd->gt($svcEnd)) {
            return back()->withErrors([
                'start_time' =>
                    "Appointment must be within operating hours "
                    . "({$service->start_time} - {$service->end_time})."
            ]);
        }

        // Capacity rules
        if (in_array($service->service_type, ['restaurant','bar','spa']) &&
            $data['number_of_guests'] > $service->capacity) {

            return back()->withErrors([
                'number_of_guests' =>
                    "Maximum {$service->capacity} guests allowed."
            ]);
        }

        // Conflict detection
        if ($this->hasConflict($service, $data, $booking->id)) {
            return back()->withErrors([
                'appointment_date' =>
                    "This service is already fully booked at that time."
            ]);
        }

        // Apply updates
        $booking->guest_name       = $data['guest_name'];
        $booking->guest_email      = $data['guest_email'];
        $booking->guest_contact    = $data['guest_contact'] ?? null;

        $booking->appointment_date = $data['appointment_date'];
        $booking->start_time       = $data['start_time'];
        $booking->end_time         = $data['end_time'];

        $booking->number_of_guests = $data['number_of_guests'];
        $booking->remarks          = $data['remarks'] ?? null;

        $booking->type                 = $data['type'] ?? $booking->type;
        $booking->booking_status       = $data['booking_status'];
        $booking->payment_status       = $data['payment_status'];
        $booking->status_change_reason = $data['status_change_reason'] ?? null;

        $booking->total_price = BookingCalculator::computeTotal($booking);

        $booking->save();

        return back()->with('success', 'Service booking updated.');
    }

    // Delete a service booking
    public function destroy(ServiceBooking $booking)
    {
        // Prevent deleting completed or cancelled bookings
        if (in_array($booking->booking_status, ['completed','cancelled'])) {
            return back()->withErrors([
                'error' => 'Cannot delete completed or cancelled bookings.'
            ]);
        }

        // Prevent delete if payments exist
        if ($booking->payments()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete this booking because payments already exist.'
            ]);
        }

        $booking->delete();

        return back()->with('success', 'Service booking deleted.');
    }

    // Conflict detection logic
    private function hasConflict(Service $service, array $data, $ignoreId = null): bool
    {
        $svcType = $service->service_type;

        $base = ServiceBooking::where('service_id', $service->id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('appointment_date', $data['appointment_date']);

        if ($ignoreId) {
            $base->where('id', '!=', $ignoreId);
        }

        // Time-overlapping bookings
        if (in_array($svcType, ['spa', 'restaurant', 'bar'])) {

            $count = (clone $base)
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->count();

            return $count >= $service->capacity;
        }

        // Date-only capacity service
        if (in_array($svcType, ['gym', 'swimming_pool'])) {
            return $base->count() >= $service->capacity;
        }

        return false;
    }
}
