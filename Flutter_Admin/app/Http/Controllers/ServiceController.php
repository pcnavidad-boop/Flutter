<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    // View services
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('service.index', compact('services'));
    }

    // Create a service
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
            'capacity'    => 'nullable|integer|min:1',
            'price_type'  => 'required|in:per_hour,per_service,per_person',
            'base_price'  => 'required|numeric|min:0|max:999999.99',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['name'] . '-' . uniqid());

        Service::create($data);

        return redirect()->route('service.index_page')->with('success', 'Service created successfully');
    }

    // Update a service
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255',Rule::unique('services','name')->ignore($service->id)],
            'description' => 'nullable|string|max:1000',
            'capacity'    => 'nullable|integer|min:1',
            'price_type'  => 'required|in:per_hour,per_service,per_person',
            'base_price'  => 'required|numeric|min:0|max:999999.99',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|in:available,occupied,maintenance',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        // Regenerate slug if name changes
        if ($data['name'] !== $service->name) {
            $data['slug'] = Str::slug($data['name'] . '-' . uniqid());
        }

        $service->update($data);

        return redirect()->route('service.index_page')->with('success', 'Service updated successfully');
    }

    // Archive a service
    public function archive(Request $request, Service $service)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        $service->update(['is_archived' => $request->is_archived]);

        return redirect()->route('service.index_page')->with('success', 'Service archived successfully');
    }

    // Delete a service
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('service.index_page')->with('success', 'Service deleted successfully');
    }
}
