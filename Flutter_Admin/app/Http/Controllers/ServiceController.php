<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // View list of services
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('service.index', compact('services'));
    }

    // Create a new service
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:services,name',
            'service_type' => ['required', Rule::in(['restaurant','spa','gym','swimming_pool','bar'])],
            'description'  => 'required|string|max:1000',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after_or_equal:start_time',
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Enforce capacity rules
        $capacityRequired = in_array($request->service_type, ['restaurant','bar','spa']);

        if ($capacityRequired) {
            $request->validate([
                'capacity' => 'required|integer|min:1'
            ]);
            $data['capacity'] = $request->capacity;
        } else {
            $data['capacity'] = $request->capacity ?: null;
        }

        // Price type assignment
        $priceMap = [
            'restaurant'     => 'per_person',
            'bar'            => 'per_person',
            'swimming_pool'  => 'per_day',
            'gym'            => 'per_day',
            'spa'            => 'per_hour',
        ];

        $data['price_type'] = $priceMap[$request->service_type];

        // Image upload handling
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        // System-controlled fields
        $data['user_id'] = auth()->id();
        $data['status'] = 'available';
        $data['is_archived'] = false;

        Service::create($data);

        return redirect()->route('service.index_page')
            ->with('success', 'Service created successfully.');
    }

    // Update a service
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'         => [
                'required','string','max:255',
                Rule::unique('services','name')->ignore($service->id)
            ],
            'service_type' => ['required', Rule::in(['restaurant','spa','gym','swimming_pool','bar'])],
            'description'  => 'required|string|max:1000',
            'capacity'     => 'nullable|integer|min:1',
            'base_price'   => 'required|numeric|min:0|max:999999.99',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after_or_equal:start_time',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'       => 'required|in:available,occupied,maintenance',
        ]);

        // Capacity rules enforcement
        $capacityRequired = in_array($request->service_type, ['restaurant','bar','spa']);

        if ($capacityRequired) {
            $request->validate([
                'capacity' => 'required|integer|min:1'
            ]);
            $data['capacity'] = $request->capacity;
        } else {
            $data['capacity'] = $request->capacity ?: null;
        }

        // Price type assignment
        $priceMap = [
            'restaurant'     => 'per_person',
            'bar'            => 'per_person',
            'swimming_pool'  => 'per_day',
            'gym'            => 'per_day',
            'spa'            => 'per_hour',
        ];

        $data['price_type'] = $priceMap[$request->service_type];

        // Image replacement logic
        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')
                ->store('service_images', 'public');
        }

        $service->update($data);

        return redirect()->route('service.index_page')
            ->with('success', 'Service updated successfully.');
    }

    // Archive a service
    public function archive(Request $request, Service $service)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        $service->update(['is_archived' => $request->is_archived]);

        return redirect()->route('service.index_page')
            ->with('success', 'Service archived successfully.');
    }

    // Delete a service
    public function destroy(Service $service)
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()
            ->route('service.index_page')
            ->with('success', 'Service deleted successfully.');
    }
}
