<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // View services
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('service.index', compact('services'));
    }

    public function viewCreatePage()
    {
        return view('service.create');
    }

    // Create service
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
            'capacity'    => 'nullable|integer|min:1',
            'price_type'  => 'required|in:per_hour,per_service,per_person',
            'base_price'  => 'required|numeric|min:0|max:99999999.99',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|in:Available,Occupied,Maintenance,Unavailable',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        $data['user_id'] = auth()->id();

        Service::create($data);

        return redirect()->route('service.index_page')->with('success', 'Service created successfully');
    }

    // Update service
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')->ignore($service->id),
            ],
            'description' => 'nullable|string|max:1000',
            'capacity'    => 'nullable|integer|min:1',
            'price_type'  => 'required|in:per_hour,per_service,per_person',
            'base_price'  => 'required|numeric|min:0|max:99999999.99',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after_or_equal:start_time',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|in:Available,Occupied,Maintenance,Unavailable',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service_images', 'public');
        }

        $service->update($data);

        return redirect()->route('service.index_page')->with('success', 'Service updated successfully');
    }

    // Archive service
    public function archive(Request $request, Service $service)
    {
        $data = $request->validate([
            'is_archived' => 'required|boolean',
        ]);

        $service->update([
            'is_archived' => $data['is_archived'],
        ]);

        return redirect()->route('service.index_page')->with('success', 'Service archived successfully');
    }

    // Delete service
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('service.index_page')->with('success', 'Service deleted successfully');
    }
}
