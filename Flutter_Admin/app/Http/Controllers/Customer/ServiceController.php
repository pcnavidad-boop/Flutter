<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\ConflictDetectionService;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('is_archived', false)
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        return view('customer.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        if ($service->is_archived || $service->status !== 'available') {
            return redirect()->route('hotel.services')
                ->withErrors(['error' => 'This service is currently unavailable.']);
        }

        return view('customer.services.show', compact('service'));
    }

    /**
     * Check service availability (customer side)
     */
    public function availability(Request $request, ConflictDetectionService $conflict)
    {
        $data = $request->validate([
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time'       => 'nullable|string',
            'guests'           => 'required|integer|min:1',
        ]);

        \Log::info('Service availability check:', $data);

        $service = Service::findOrFail($data['service_id']);

        if ($data['guests'] > $service->capacity) {
            \Log::info('Service capacity exceeded:', [
                'requested' => $data['guests'],
                'capacity' => $service->capacity
            ]);
            return response()->json(false);
        }

        // Build proper data array for conflict detection
        $conflictData = [
            'appointment_date' => $data['appointment_date'],
            'number_of_guests' => $data['guests'],
        ];

        // For time-based services, add start/end times
        if (in_array($service->service_type, ['spa', 'restaurant', 'bar'])) {
            if (!empty($data['start_time'])) {
                $conflictData['start_time'] = $data['start_time'];
                
                // Calculate end_time (1 hour slot)
                $startHour = (int) explode(':', $data['start_time'])[0];
                $conflictData['end_time'] = sprintf('%02d:00:00', $startHour + 1);
            }
        }

        $hasConflict = $conflict->serviceHasConflict(
            $service,
            $conflictData,
            null // ignoreId
        );

        \Log::info('Service conflict check result:', [
            'service_id' => $service->id,
            'conflict_data' => $conflictData,
            'has_conflict' => $hasConflict,
            'available' => !$hasConflict
        ]);

        return response()->json(!$hasConflict);
    }

    /**
     * Disabled dates for Flatpickr
     */
    public function bookedDates(Service $service)
    {
        $dates = $service->bookings()
            ->where('booking_status', 'confirmed')
            ->pluck('appointment_date')
            ->unique()
            ->values()
            ->toArray();

        \Log::info('Service booked dates:', [
            'service_id' => $service->id,
            'dates' => $dates
        ]);

        return response()->json($dates);
    }

    /**
     * Return booked guest counts per time slot for a given date
     * Response format:
     * {
     *   "10:00": 2,
     *   "11:00": 5
     * }
     */
    public function bookedSlots(Service $service, Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json([]);
        }

        $slots = $service->bookings()
            ->where('booking_status', 'confirmed')
            ->where('appointment_date', $date)
            ->selectRaw('start_time, SUM(number_of_guests) as total_guests')
            ->groupBy('start_time')
            ->pluck('total_guests', 'start_time')
            ->mapWithKeys(function ($count, $time) {
                return [substr($time, 0, 5) => (int) $count];
            });

        return response()->json($slots);
    }

}