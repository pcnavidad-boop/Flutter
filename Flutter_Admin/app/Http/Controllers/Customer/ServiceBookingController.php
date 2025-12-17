<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServiceBookingController extends Controller
{
    /**
     * Show service booking form
     */
    public function createPage(Service $service, Request $request)
    {
        // Always required
        if (
            !$request->appointment_date ||
            !$request->guests
        ) {
            return redirect()
                ->route('hotel.service.show', $service)
                ->with('error', 'Please select date and guests first.');
        }

        // Only required for time-based services
        if (
            in_array($service->service_type, ['spa', 'restaurant', 'bar']) &&
            (
                !$request->start_time ||
                !$request->end_time
            )
        ) {
            return redirect()
                ->route('hotel.service.show', $service)
                ->with('error', 'Please select a time slot first.');
        }

        return view('customer.bookings.service.create', [
            'service' => $service,
            'appointment_date' => $request->appointment_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'guests' => $request->guests,
        ]);
    }

    /**
     * Store service booking
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id'        => 'required|exists:services,id',
            'guest_name'        => 'required|string|max:255',
            'guest_email'       => 'required|email|max:255',
            'guest_contact'     => 'nullable|string|max:11',
            'appointment_date'  => 'required|date',
            'start_time'        => 'required|string',
            'end_time'          => 'required|string',
            'number_of_guests'  => 'required|integer|min:1',
            'remarks'           => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($data['service_id']);

        // Capacity check
        if ($data['number_of_guests'] > ($service->capacity ?? PHP_INT_MAX)) {
            return back()
                ->withErrors([
                    'number_of_guests' =>
                        "This service supports up to {$service->capacity} guests."
                ])
                ->withInput();
        }

        try {
            $booking = ServiceBooking::create([
                'reference'         => strtoupper(Str::random(10)),
                'service_id'        => $service->id,
                'guest_name'        => $data['guest_name'],
                'guest_email'       => $data['guest_email'],
                'guest_contact'     => $data['guest_contact'],
                'appointment_date'  => Carbon::parse($data['appointment_date']),
                'start_time'        => $data['start_time'],
                'end_time'          => $data['end_time'],
                'number_of_guests'  => $data['number_of_guests'],
                'total_price'       => $service->base_price,
                'booking_status'    => 'confirmed',
                'remarks'           => $data['remarks'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Service booking failed', [
                'error'      => $e->getMessage(),
                'service_id'=> $service->id,
            ]);

            return back()
                ->withErrors([
                    'error' => 'Unable to complete booking. Please try again.'
                ])
                ->withInput();
        }

        return redirect()->route(
            'hotel.book.service.summary',
            $booking->reference
        );
    }

    /**
     * Booking summary
     */
    public function summary(string $reference)
    {
        $booking = ServiceBooking::where('reference', $reference)
            ->with('service')
            ->firstOrFail();

        return view('customer.bookings.service.summary', [
            'booking' => $booking,
        ]);
    }
}
