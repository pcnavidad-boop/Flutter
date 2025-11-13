<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    // View rooms
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();
        return view('room.index', compact('rooms'));
    }

    // Show create page (optional)
    public function viewCreatePage()
    {
        return view('room.create');
    }

    // Create room
    public function create(Request $request)
    {
        $data = $request->validate([
            'room_number'   => 'required|string|max:255|unique:rooms,room_number',
            'type'          => 'required|in:Single,Double,Quad,Family,Suite,Penthouse,Function',
            'price_type'    => 'required|in:per_night,per_hour,per_event',
            'base_price'    => 'required|numeric|min:0|max:99999999.99',
            'is_time_based' => 'boolean',
            'number_of_beds'=> 'nullable|integer|min:1',
            'capacity'      => 'required|integer|min:1',
            'status'        => 'required|in:Available,Occupied,Maintenance,Unavailable',
            'description'   => 'nullable|string|max:1000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

        $data['user_id'] = auth()->id();

        Room::create($data);

        return redirect()->route('room.index_page')->with('success', 'Room created successfully');
    }

    // Update room
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_number'   => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'room_number')->ignore($room->id),
            ],
            'type'          => 'required|in:Single,Double,Quad,Family,Suite,Penthouse,Function',
            'price_type'    => 'required|in:per_night,per_hour,per_event',
            'base_price'    => 'required|numeric|min:0|max:99999999.99',
            'is_time_based' => 'boolean',
            'number_of_beds'=> 'nullable|integer|min:1',
            'capacity'      => 'required|integer|min:1',
            'status'        => 'required|in:Available,Occupied,Maintenance,Unavailable',
            'description'   => 'nullable|string|max:1000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

        $room->update($data);

        return redirect()->route('room.index_page')->with('success', 'Room updated successfully');
    }

    // Archive room
    public function archive(Request $request, Room $room)
    {
        $data = $request->validate([
            'is_archived' => 'required|boolean',
        ]);

        $room->update([
            'is_archived' => $data['is_archived'],
        ]);

        return redirect()->route('room.index_page')->with('success', 'Room archived successfully');
    }

    // Delete room
    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('room.index_page')->with('success', 'Room deleted successfully');
    }
}
