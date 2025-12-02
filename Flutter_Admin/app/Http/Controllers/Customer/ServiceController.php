<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;

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
}
