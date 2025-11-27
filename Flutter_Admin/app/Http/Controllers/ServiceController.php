<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ServiceController extends Controller
{
    // View all services
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('service.index', compact('services'));
    }

    // Create a new service
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'         => [
                'required','string','max:255',
                Rule::unique('services','name')->where(function($q){
                    return $q->where('is_archived', false);
                })
            ],
            'service_type' => ['required', Rule::in(['restaurant','spa','gym','swimming_pool','bar'])],
            'description'  => 'required|string|max:1000',
            'location'     => 'required|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Price type rules
        $priceMap = [
            'restaurant'     => 'per_person',
            'bar'            => 'per_person',
            'spa'            => 'per_hour',
            'gym'            => 'per_day',
            'swimming_pool'  => 'per_day',
        ];

        $data['price_type'] = $priceMap[$request->service_type];

        // Capacity rules
        $capacityRequired = in_array($request->service_type, ['restaurant','bar','spa']);

        if ($capacityRequired) {
            $request->validate(['capacity' => 'required|integer|min:1']);
            $data['capacity'] = $request->capacity;
        } else {
            // For gym/pool, default capacity > 1 makes sense
            $data['capacity'] = $request->capacity ?: 20;
        }

        // Image upload
        $data['image'] = $request->file('image')->store('service_images', 'public');

        // System fields
        $data['created_by'] = auth()->id();
        $data['status']     = 'available';
        $data['is_archived'] = false;

        Service::create($data);

        return redirect()->route('service.index_page')
            ->with('success', 'Service created successfully.');
    }

    // Update a service
    public function update(Request $request, Service $service)
    {
        // ❗ Prevent updating archived service (same rule as Rooms)
        if ($service->is_archived) {
            return back()->withErrors([
                'error' => 'Archived services cannot be modified.'
            ]);
        }

        $data = $request->validate([
            'name'         => [
                'required','string','max:255',
                Rule::unique('services','name')
                    ->where(fn($q) => $q->where('is_archived', false))
                    ->ignore($service->id)
            ],
            'service_type' => ['required', Rule::in(['restaurant','spa','gym','swimming_pool','bar'])],
            'description'  => 'required|string|max:1000',
            'location'     => 'required|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'       => 'required|in:available,maintenance',
        ]);

        // ❗ Prevent changing service_type if bookings exist
        if ($service->bookings()->where('booking_status', '!=', 'cancelled')->exists()) {
            if ($request->service_type !== $service->service_type) {
                return back()->withErrors([
                    'service_type' => 'Cannot change service type while bookings exist.'
                ]);
            }
        }

        // ❗ Prevent setting maintenance if active bookings exist
        if ($request->status === 'maintenance' &&
            $service->bookings()->where('booking_status','!=','cancelled')->exists()) {

            return back()->withErrors([
                'status' => 'Cannot put service under maintenance because active bookings exist.'
            ]);
        }

        // Validate new operating hours against existing bookings
        $existingBookings = $service->bookings()
            ->where('booking_status', '!=', 'cancelled')
            ->get();

        foreach ($existingBookings as $b) {

            $reqStart = Carbon::parse($data['start_time']);
            $reqEnd   = Carbon::parse($data['end_time']);

            $bStart   = Carbon::parse($b->start_time);
            $bEnd     = Carbon::parse($b->end_time);

            if ($bStart->lt($reqStart) || $bEnd->gt($reqEnd)) {
                return back()->withErrors([
                    'start_time' =>
                        "Cannot change operating hours: existing bookings fall outside the new schedule."
                ]);
            }
        }

        // Price type auto-set
        $priceMap = [
            'restaurant'     => 'per_person',
            'bar'            => 'per_person',
            'spa'            => 'per_hour',
            'gym'            => 'per_day',
            'swimming_pool'  => 'per_day',
        ];

        $data['price_type'] = $priceMap[$request->service_type];

        // Capacity rules
        $capacityRequired = in_array($request->service_type, ['restaurant','bar','spa']);

        if ($capacityRequired) {

            // Cannot reduce capacity below booked guest count
            foreach ($existingBookings as $b) {
                if ($b->number_of_guests > $request->capacity) {
                    return back()->withErrors([
                        'capacity' =>
                            "Cannot set capacity lower than existing bookings ({$b->number_of_guests} guests)."
                    ]);
                }
            }

            $request->validate(['capacity' => 'required|integer|min:1']);
            $data['capacity'] = $request->capacity;

        } else {
            $data['capacity'] = $request->capacity ?: 20;
        }

        // Image replacement
        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        $service->update($data);

        return redirect()->route('service.index_page')
            ->with('success', 'Service updated successfully.');
    }

    // Archive / Unarchive
    public function archive(Request $request, Service $service)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        // ❗ Prevent archiving if the service has **future** bookings
        $hasFuture = $service->bookings()
            ->where('booking_status','!=','cancelled')
            ->where('appointment_date','>=',today())
            ->exists();

        if ($hasFuture) {
            return back()->withErrors([
                'error' => 'Cannot archive a service with future bookings.'
            ]);
        }

        $service->update(['is_archived' => $request->is_archived]);

        return redirect()->route('service.index_page')
            ->with('success', 'Service archive status updated.');
    }

    // Delete a service
    public function destroy(Service $service)
    {
        // ❗ Cannot delete unless archived first
        if (!$service->is_archived) {
            return back()->withErrors([
                'error' => 'You must archive this service before deleting it.'
            ]);
        }

        // ❗ Prevent deletion if any bookings exist (past or future)
        if ($service->bookings()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete this service because bookings exist.'
            ]);
        }

        // Delete image
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()
            ->route('service.index_page')
            ->with('success', 'Service deleted successfully.');
    }
}

