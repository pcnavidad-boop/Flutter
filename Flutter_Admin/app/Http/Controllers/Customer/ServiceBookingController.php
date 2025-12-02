<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Services\BookingCalculator;
use App\Services\ConflictDetectionService;
use App\Services\BookingLifecycleService;
use Carbon\Carbon;

class ServiceBookingController extends Controller
{
    protected ConflictDetectionService $conflict;
    protected BookingLifecycleService $lifecycle;

    public function __construct(
        ConflictDetectionService $conflict,
        BookingLifecycleService $lifecycle
    ) {
        $this->conflict = $conflict;
        $this->lifecycle = $lifecycle;
    }

    public function createPage(Request $request)
    {
        $services = Service::active()->available()->orderBy('name')->get();
        $selectedService = $request->service_id ? Service::find($request->service_id) : null;

        return view('customer.bookings.service.create', compact('services', 'selectedService'));
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
        ]);

        $service = Service::findOrFail($data['service_id']);

        try {
            $this->lifecycle->assertItemBookable($service);
        } catch (\Exception $e) {
            return back()->withErrors(['service_id' => $e->getMessage()]);
        }

        // Operating hours
        if ($data['start_time'] < $service->start_time ||
            $data['end_time'] > $service->end_time) {
            return back()->withErrors([
                'start_time' => "Appointment must be within operating hours: {$service->start_time} - {$service->end_time}",
            ]);
        }

        // Capacity
        if (in_array($service->service_type, ['restaurant', 'bar', 'spa']) &&
            $data['number_of_guests'] > $service->capacity) {
            return back()->withErrors([
                'number_of_guests' => "Maximum {$service->capacity} guests allowed.",
            ]);
        }

        // Conflict Check
        if ($this->conflict->serviceHasConflict($service, $data)) {
            return back()->withErrors([
                'appointment_date' => "This service is fully booked at the specified time.",
            ]);
        }

        // CREATE BOOKING (pending)
        $booking = new ServiceBooking();
        $booking->fill($data);
        $booking->reference = ServiceBooking::generateReference();
        $booking->booking_status = 'pending';
        $booking->payment_status = 'unpaid';
        $booking->type = 'website';
        $booking->created_by = null;
        $booking->save();

        // Compute total
        $booking->total_price = BookingCalculator::computeTotal($booking);
        $booking->save();

        return redirect()->route('hotel.booking.service.summary', $booking->reference)
            ->with('success', 'Booking saved! Please proceed to payment.');
    }

    public function summary($reference)
    {
        $booking = ServiceBooking::with('service')
            ->where('reference', $reference)
            ->firstOrFail();

        return view('customer.bookings.service.summary', compact('booking'));
    }
}
