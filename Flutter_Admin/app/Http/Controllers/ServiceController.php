<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\ServiceRulesService;
use App\Services\ItemLifecycleService;
use Carbon\Carbon;

class ServiceController extends Controller
{
    protected ServiceRulesService $rules;
    protected ItemLifecycleService $lifecycle;

    public function __construct(ServiceRulesService $rules, ItemLifecycleService $lifecycle)
    {
        $this->rules = $rules;
        $this->lifecycle = $lifecycle;
    }

    // List services
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('service.index', compact('services'));
    }

    // Create service
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255', Rule::unique('services','name')->where(fn($q) => $q->where('is_archived', false))],
            'service_type' => 'required|in:restaurant,spa,gym,swimming_pool,bar',
            'description'  => 'required|string|max:1000',
            'location'     => 'required|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Determine price type and validated capacity
        $data['price_type'] = $this->rules->determinePriceType($data['service_type']);
        try {
            $data['capacity'] = $this->rules->validateCapacity($data['service_type'], $request->capacity ?? null);
            $this->rules->validateOperatingHours($data['start_time'], $data['end_time']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        // Upload image
        $data['image'] = $request->file('image')->store('service_images', 'public');

        // System fields
        $data['created_by']  = auth()->id();
        $data['status']      = 'available';
        $data['is_archived'] = false;

        Service::create($data);

        return redirect()->route('service.index_page')->with('success', 'Service created successfully.');
    }

    // Update service
    public function update(Request $request, Service $service)
    {
        if ($service->is_archived) {
            return back()->withErrors(['error' => 'Cannot update an archived service.']);
        }

        $data = $request->validate([
            'name'         => ['required','string','max:255', Rule::unique('services','name')->where(fn($q) => $q->where('is_archived', false))->ignore($service->id)],
            'service_type' => 'required|in:restaurant,spa,gym,swimming_pool,bar',
            'description'  => 'required|string|max:1000',
            'location'     => 'required|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'       => 'required|in:available,maintenance',
        ]);

        // Prevent changing service type when active bookings exist
        if ($service->bookings()->where('booking_status','!=','cancelled')->exists() && $data['service_type'] !== $service->service_type) {
            return back()->withErrors(['service_type' => 'Cannot change service type while bookings exist.']);
        }

        // Prevent putting to maintenance if active bookings exist
        if ($data['status'] === 'maintenance' && $service->bookings()->where('booking_status','!=','cancelled')->exists()) {
            return back()->withErrors(['status' => 'Cannot place service under maintenance while bookings exist.']);
        }

        // Validate capacity and operating hours against existing bookings
        $existingBookings = $service->bookings()->where('booking_status','!=','cancelled')->get();

        try {
            // price type + capacity validation
            $data['price_type'] = $this->rules->determinePriceType($data['service_type']);
            $data['capacity'] = $this->rules->validateCapacity($data['service_type'], $request->capacity ?? $service->capacity);
            $this->rules->validateOperatingHours($data['start_time'], $data['end_time']);

            // Verify capacity doesn't break any existing booking
            foreach ($existingBookings as $b) {
                if ($b->number_of_guests > $data['capacity']) {
                    return back()->withErrors(['capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."]);
                }
            }

            // Verify operating hours won't leave bookings outside schedule
            foreach ($existingBookings as $b) {
                $reqStart = \Carbon\Carbon::parse($data['start_time']);
                $reqEnd   = \Carbon\Carbon::parse($data['end_time']);
                $bStart   = \Carbon\Carbon::parse($b->start_time);
                $bEnd     = \Carbon\Carbon::parse($b->end_time);

                if ($bStart->lt($reqStart) || $bEnd->gt($reqEnd)) {
                    return back()->withErrors(['start_time' => "Cannot change operating hours: existing booking at {$b->start_time}-{$b->end_time} would be outside new hours."]);
                }
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        // Replace image safely
        if ($request->hasFile('image')) {
            $new = $request->file('image')->store('service_images', 'public');
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $new;
        }

        $service->update($data);

        return redirect()->route('service.index_page')->with('success', 'Service updated successfully.');
    }

    // Archive / unarchive (no delete)
    public function archive(Request $request, Service $service)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        try {
            $this->lifecycle->assertCanArchive($service);
        } catch (\Exception $e) {
            return back()->withErrors(['is_archived' => $e->getMessage()]);
        }

        $service->update(['is_archived' => $request->is_archived]);

        return redirect()->route('service.index_page')->with('success', 'Service archive status updated.');
    }
}
