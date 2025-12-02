<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Service;
use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'rooms'          => Room::count(),
            'services'       => Service::count(),
            'bookings'       => RoomBooking::count() + ServiceBooking::count(),
            'payments'       => Payment::sum('amount'),
            'recentPayments' => Payment::latest()->take(5)->get(),
        ]);
    }
}
