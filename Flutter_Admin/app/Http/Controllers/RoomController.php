<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'base_price'     => 'required|numeric|min:0|max:999999.99',
            'description'    => 'required|string|max:1000',
            'image'          => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // ROOM-TYPE BASED LOGIC
        if ($request->room_type === 'function') {

            // FUNCTION ROOM LOGIC
            $data['price_type'] = 'per_event_per_day';
            $data['number_of_beds'] = null;
            $data['capacity'] = $request->validate([
                'capacity' => 'required|integer|min:1|max:250'
            ])['capacity'];

        } else {

            // STAY ROOM LOGIC
            $data['price_type'] = 'per_night';

            // Enforce bed limits per stay room type
            $bedRules = [
                'single'     => ['min' => 1, 'max' => 1],
                'double'     => ['min' => 1, 'max' => 2],
                'quad'       => ['min' => 2, 'max' => 2],
                'family'     => ['min' => 2, 'max' => 3],
                'suite'      => ['min' => 1, 'max' => 2],
                'penthouse'  => ['min' => 2, 'max' => 4],
            ];

            $type = $request->room_type;

            // Validate number_of_beds
            $request->validate([
                'number_of_beds' => "required|integer|min:{$bedRules[$type]['min']}|max:{$bedRules[$type]['max']}",
            ]);

            $data['number_of_beds'] = $request->number_of_beds;

            // Enforce capacity limits for stay rooms
            $capacityRules = [
                'single'     => ['min' => 1, 'max' => 1],
                'double'     => ['min' => 2, 'max' => 2],
                'quad'       => ['min' => 4, 'max' => 4],
                'family'     => ['min' => 4, 'max' => 6],
                'suite'      => ['min' => 2, 'max' => 4],
                'penthouse'  => ['min' => 4, 'max' => 8],
            ];

            $request->validate([
                'capacity' => "required|integer|min:{$capacityRules[$type]['min']}|max:{$capacityRules[$type]['max']}"
            ]);

            $data['capacity'] = $request->capacity;
        }

        // Image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

        // System-controlled fields
        $data['user_id'] = auth()->id();
        $data['status'] = 'available';
        $data['is_archived'] = false;

        Room::create($data);

        return redirect()->route('room.index_page')
            ->with('success', 'Room created successfully.');
    }

    // Update a room
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:255', Rule::unique('rooms','name')->ignore($room->id)],
            'room_number'    => ['required','string','max:255', Rule::unique('rooms','room_number')->ignore($room->id)],
            'room_type'      => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'     => 'required|numeric|min:0|max:999999.99',
            'description'    => 'required|string|max:1000',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'         => 'required|in:available,occupied,maintenance',
        ]);

        // ROOM-TYPE BASED LOGIC
        if ($request->room_type === 'function') {

            $data['price_type'] = 'per_event_per_day';
            $data['number_of_beds'] = null;

            $data['capacity'] = $request->validate([
                'capacity' => 'required|integer|min:1|max:250'
            ])['capacity'];

        } else {

            $data['price_type'] = 'per_night';

            // Bed rules
            $bedRules = [
                'single'     => ['min' => 1, 'max' => 1],
                'double'     => ['min' => 1, 'max' => 2],
                'quad'       => ['min' => 2, 'max' => 2],
                'family'     => ['min' => 2, 'max' => 3],
                'suite'      => ['min' => 1, 'max' => 2],
                'penthouse'  => ['min' => 2, 'max' => 4],
            ];

            $type = $request->room_type;

            $request->validate([
                'number_of_beds' => "required|integer|min:{$bedRules[$type]['min']}|max:{$bedRules[$type]['max']}"
            ]);

            $data['number_of_beds'] = $request->number_of_beds;

            // Capacity rules
            $capacityRules = [
                'single'     => ['min' => 1, 'max' => 1],
                'double'     => ['min' => 2, 'max' => 2],
                'quad'       => ['min' => 4, 'max' => 4],
                'family'     => ['min' => 4, 'max' => 6],
                'suite'      => ['min' => 2, 'max' => 4],
                'penthouse'  => ['min' => 4, 'max' => 8],
            ];

            $request->validate([
                'capacity' => "required|integer|min:{$capacityRules[$type]['min']}|max:{$capacityRules[$type]['max']}"
            ]);

            $data['capacity'] = $request->capacity;
        }

        // Image replacement logic
        if ($request->hasFile('image')) {
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

        $room->update($data);

        return redirect()->route('room.index_page')
            ->with('success', 'Room updated successfully.');
    }

    // Archive a room
    public function archive(Request $request, Room $room)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        $room->update(['is_archived' => $request->is_archived]);

        return redirect()->route('room.index_page')
            ->with('success', 'Room archived successfully.');
    }

    // Delete a room
    public function destroy(Room $room)
    {
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()->route('room.index_page')
            ->with('success', 'Room deleted successfully.');
    }
}
