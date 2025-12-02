<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    // List services with filters and pagination
    public function index(Request $request)
    {
        $q = Service::query();

        // Search (name, location)
        if ($s = $request->input('search')) {
            $q->where(function($sub) use ($s) {
                $sub->where('name', 'like', "%{$s}%")
                    ->orWhere('location', 'like', "%{$s}%");
            });
        }

        // Type filter
        if ($type = $request->input('type')) {
            $q->where('service_type', $type);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $q->where('status', $status);
        }

        // Archived filter (0 or 1)
        if ($request->has('archived') && $request->input('archived') !== '') {
            $q->where('is_archived', (bool) $request->input('archived'));
        }

        $services = $q->orderBy('name')->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    // Show service details (returns formatted price fields — same pattern as rooms)
    public function show(Service $service)
    {
        return response()->json([
            'id'                 => $service->id,
            'name'               => $service->name,
            'location'           => $service->location,
            'service_type'       => $service->service_type,
            'description'        => $service->description,
            'capacity'           => $service->capacity,
            'price_type'         => $service->formatted_price_type,
            'base_price'         => $service->formatted_base_price,
            'start_time'         => substr($service->start_time, 0, 5),
            'end_time'           => substr($service->end_time, 0, 5),
            'status'             => $service->status,
            'is_archived'        => $service->is_archived,
            'image_url'          => asset('storage/' . $service->image),
            'created_at'         => $service->created_at->format('M d, Y h:i A'),
        ]);
    }

    // Create a new service
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255', Rule::unique('services','name')->where(fn($q) => $q->where('is_archived', false))],
            'service_type' => 'required|in:restaurant,spa,gym,swimming_pool,bar',
            'description'  => 'nullable|string|max:1000',
            'location'     => 'nullable|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $type = $data['service_type'];

        // Choose price_type here (same approach as rooms)
        if ($type === 'spa') {
            $data['price_type'] = 'per_hour';
        } elseif (in_array($type, ['restaurant','bar'])) {
            $data['price_type'] = 'per_person';
        } else {
            $data['price_type'] = 'per_day';
        }

        try {
            $data['capacity'] = $this->rules->validateCapacity($type, $request->capacity ?? null);
            $this->rules->validateOperatingHours($data['start_time'], $data['end_time']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        /* Store image */
        $data['image'] = $request->file('image')->store('service_images', 'public');

        $data['created_by']  = auth()->id();
        $data['status']      = 'available';
        $data['is_archived'] = false;

        Service::create($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    // Update an existing service
    public function update(Request $request, Service $service)
    {
        if ($service->is_archived) {
            return back()->withErrors(['error' => 'Cannot update an archived service.'])->withInput();
        }

        $data = $request->validate([
            'name'         => ['required','string','max:255', Rule::unique('services','name')->where(fn($q) => $q->where('is_archived', false))->ignore($service->id)],
            'service_type' => 'required|in:restaurant,spa,gym,swimming_pool,bar',
            'description'  => 'nullable|string|max:1000',
            'location'     => 'nullable|string|max:255',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'       => 'required|in:available,maintenance',
        ]);

        $type = $data['service_type'];
        $activeBookings = $service->bookings()->where('booking_status','!=','cancelled')->get();

        if ($activeBookings->count() > 0 && $type !== $service->service_type) {
            return back()->withErrors(['service_type' => 'Cannot change service type while bookings exist.'])->withInput();
        }

        // Choose price_type like store()
        if ($type === 'spa') {
            $data['price_type'] = 'per_hour';
        } elseif (in_array($type, ['restaurant','bar'])) {
            $data['price_type'] = 'per_person';
        } else {
            $data['price_type'] = 'per_day';
        }

        try {
            $newCap = $this->rules->validateCapacity($type, $request->capacity ?? null);
            $this->rules->validateOperatingHours($data['start_time'], $data['end_time']);

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $newCap) {
                    return back()->withErrors(['capacity' =>
                        "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ])->withInput();
                }
            }

            $data['capacity'] = $newCap;
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        if ($request->hasFile('image')) {
            $new = $request->file('image')->store('service_images', 'public');

            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }

            $data['image'] = $new;
        }

        $service->update($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    // Archive or unarchive a service
    public function archive(Request $request, Service $service)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        try {
            $this->lifecycle->assertCanArchive($service);
        } catch (\Exception $e) {
            return back()->withErrors(['is_archived' => $e->getMessage()]);
        }

        $service->update(['is_archived' => $request->is_archived]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service archive status updated.');
    }
}
