<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    // View rooms
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();
        return view('room.index', compact('rooms'));
    }

    // Create a room
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255|unique:rooms,name',
            'room_number'    => 'required|string|max:255|unique:rooms,room_number',
            'room_type'      => 'required|in:single,double,quad,family,suite,penthouse,function',
            'price_type'     => 'required|in:per_night,per_hour',
            'base_price'     => 'required|numeric|min:0|max:999999.99',
            'number_of_beds' => 'nullable|integer|min:1',
            'capacity'       => 'required|integer|min:1',
            'description'    => 'nullable|string|max:1000',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['name'] . '-' . uniqid());

        Room::create($data);

        return redirect()->route('room.index_page')->with('success', 'Room created successfully');
    }

    // Update a room
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255|unique:rooms,name',
            'room_number'    => 'required|string|max:255|unique:rooms,room_number',
            'room_type'      => 'required|in:single,double,quad,family,suite,penthouse,function',
            'price_type'     => 'required|in:per_night,per_hour',
            'base_price'     => 'required|numeric|min:0|max:999999.99',
            'number_of_beds' => 'nullable|integer|min:1',
            'capacity'       => 'required|integer|min:1',
            'description'    => 'nullable|string|max:1000',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'         => 'required|in:available,occupied,maintenance',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

        // Regenerate slug if name changes
        if ($data['name'] !== $room->name) {
            $data['slug'] = Str::slug($data['name'] . '-' . uniqid());
        }

        $room->update($data);

        return redirect()->route('room.index_page')->with('success', 'Room updated successfully');
    }

    // Archive a room
    public function archive(Request $request, Room $room)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        $room->update(['is_archived' => $request->is_archived]);

        return redirect()->route('room.index_page')->with('success', 'Room archived successfully');
    }

    // Delete a room
    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('room.index_page')->with('success', 'Room deleted successfully');
    }
}
