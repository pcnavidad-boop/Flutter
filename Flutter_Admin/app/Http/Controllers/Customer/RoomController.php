<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Room;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::where('is_archived', false)
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('customer.rooms.index', compact('rooms'));
    }

    public function show(Room $room)
    {
        if ($room->is_archived || $room->status !== 'available') {
            return redirect()->route('hotel.rooms')
                ->withErrors(['error' => 'This room is no longer available.']);
        }

        return view('customer.rooms.show', compact('room'));
    }
}
