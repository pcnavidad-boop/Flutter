<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    //Not used (for now)
    public function viewCreatePage(){
        return view('service.create');
    }

    //View services
    public function index()
    {
        $services = Service::orderBy('service_name')->get();

        //dd($services);
        //xxx.xxxx.xxxx means folder xxx folder xxxx file xxxx.blade.php
        return view('service.index', compact('services'));
    }

    public function create(Request $request)
    {
        //Validation
        //dd($request);
        $data = $request->validate([
            'service_name' => 'required|string|max:255|unique:services,service_name',
            'service_description' => 'nullable|string|max:1000',
            'service_capacity' => 'nullable|integer|min:1',
            'price_type' => 'required|string|in:fixed,per_hour,per_service,per_person', 
            'base_price' => 'required|numeric|min:0|max:99999999.99',
            'service_start_time' => 'nullable|date_format:H:i',
            'service_end_time' => 'nullable|date_format:H:i|after_or_equal:service_start_time',
            //'service_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Default values
        $data['service_availability_status'] = true; // Always available when created

        // Optional: Handle image upload
        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')->store('service_images', 'public');
            $data['service_image'] = $imagePath;
        }

        // Create new service record
        Service::create($data);

        // Redirect back to the service index page with success message
        return redirect()->route('service.index_page')->with('success', 'Service created successfully');
    }

    use Illuminate\Validation\Rule;

    public function update(Request $request, \App\Models\Service $service)
    {
        // Validate input
        $data = $request->validate([
            'service_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'service_name')->ignore($service->id),
            ],
            'service_description' => 'nullable|string|max:1000',
            'service_capacity' => 'nullable|integer|min:1',
            'price_type' => 'required|string|in:fixed,per_hour,per_service,per_person', 
            'base_price' => 'required|numeric|min:0|max:99999999.99',
            'service_start_time' => 'nullable|date_format:H:i',
            'service_end_time' => 'nullable|date_format:H:i|after_or_equal:service_start_time',
            'service_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Boolean Update
        $data['service_availability_status'] = $request->has('service_availability_status')
            ? (int) $request->input('service_availability_status')
            : 0;

        // Optional: Handle image upload
        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')->store('service_images', 'public');
            $data['service_image'] = $imagePath;
        }

        // Update the service
        $service->update($data);

        // Redirect back with success message
        return redirect()->route('service.index_page')->with('success', 'Service updated successfully');
    }

    public function archive(Request $request, Service $service)
    {
        // Validate input (optional if you just toggle archive)
        $data = $request->validate([
            'is_archived' => 'required|boolean',
        ]);

        // Update the archive status
        $service->update([
            'is_archived' => $data['is_archived'],
        ]);

        return redirect()->route('service.index_page')->with('success', 'Service archived successfully');
    }

    public function destroy(\App\Models\Service $service)
    {
        // Delete the service
        $service->delete();

        return redirect()->route('service.index_page')->with('success', 'Service deleted successfully');
    }
}
