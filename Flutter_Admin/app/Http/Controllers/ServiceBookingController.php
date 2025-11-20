<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Notifications\NewServiceBookingNotification;
use Illuminate\Validation\Rule;

class ServiceBookingController extends Controller
{
    // View service bookings
    public function index()
    {
        $bookings = ServiceBooking::with('service')
            ->orderBy('booking_date', 'desc')
            ->get();

        return view('service_booking.index', compact('bookings'));
    }

    public function viewCreatePage()
    {
        $services = Service::active()->available()->get();
        return view('service_booking.create', compact('services'));
    }

    // Create a booking
    public function create(Request $request)
    {
        $data = $request->validate([
            // Guest Details
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',

            // Booking Details
            'service_id'       => 'required|exists:services,id',

            'number_of_guests' => 'required|integer|min:1',
            'appointment_date' => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['required', Rule::in(['website','walk-in','phone','email'])],
            'booking_date'     => 'required|date',

            'booking_status'   => 'confirmed',
            'payment_status'   => 'required|in:downpayment,fully_paid',
        ]);

        $data['user_id'] = auth()->id();

        // Generate unique reference
        $data['reference'] = 'SB-' . strtoupper(Str::random(8));

        // Save booking
        $booking = ServiceBooking::create($data);

        // Notify admins
        foreach (User::where('role', 'admin')->get() as $admin) {
            $admin->notify(new NewServiceBookingNotification($booking));
        }

        return redirect()
            ->route('service_booking.index_page')
            ->with('success', 'Service booking created.');
    }

    // Update a booking
    public function update(Request $request, ServiceBooking $booking)
    {
        $data = $request->validate([
            'guest_name'       => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_contact'    => 'nullable|string|max:11',

            'service_id'       => 'required|exists:services,id',

            'number_of_guests' => 'required|integer|min:1',
            'appointment_date' => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after_or_equal:start_time',

            'remarks'          => 'nullable|string|max:2000',
            'type'             => ['required', Rule::in(['website','walk-in','phone','email'])],
            'booking_date'     => 'required|date',

            'booking_status'   => ['required', Rule::in(['confirmed','cancelled','completed'])],
            'payment_status'   => ['required', Rule::in(['downpayment','fully_paid','refunded'])],
        ]);

        $booking->update($data);

        return back()->with('success', 'Service booking updated.');
    }

    // Delete a booking
    public function destroy(ServiceBooking $booking)
    {
        $booking->delete();
        return back()->with('success', 'Service booking deleted.');
    }
}
