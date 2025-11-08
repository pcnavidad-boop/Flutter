<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    //Not used (for now)
    public function viewCreatePage(){
        return view('room.create');
    }

    //View rooms
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();

        //dd($rooms);
        //xxx.xxxx.xxxx means folder xxx folder xxxx file xxxx.blade.php
        return view('room.index', compact('rooms'));
    }

    public function create(Request $request)
    {
        //Validation
        //dd($request);
        $data = $request->validate([
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'room_type' => 'nullable|string|max:255',
            'price_per_night' => 'required|numeric|min:0|max:99999999.99',
            'number_of_beds' => 'nullable|integer|min:1',
            'room_capacity' => 'required|integer|min:1',
            'room_description' => 'nullable|string|max:1000',
            //'room_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ]);

        // Default values
        $data['room_availability_status'] = true; // Always available when created

        // Optional: Handle image upload
        if ($request->hasFile('room_image')) {
            $imagePath = $request->file('room_image')->store('room_images', 'public');
            $data['room_image'] = $imagePath;
        }

        // Create new room record
        Room::create($data);

        // Redirect back to the room index page with success message
        return redirect()->route('room.index_page')->with('success', 'Room created successfully');
    }

    public function update(Request $request, Room $room)
    {
        // Validate input
        $data = $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'room_number')->ignore($room->id),
            ],
            'room_type' => 'nullable|string|max:255',
            'price_per_night' => 'required|numeric|min:0|max:99999999.99',
            'number_of_beds' => 'nullable|integer|min:1',
            'room_capacity' => 'required|integer|min:1',
            'room_description' => 'nullable|string|max:1000',
            //'room_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ]);

        // Boolean Update
        $data['room_availability_status'] = $request->has('room_availability_status')
            ? (int) $request->input('room_availability_status')
            : 0;

        // Optional: Handle image upload
        if ($request->hasFile('room_image')) {
            $imagePath = $request->file('room_image')->store('room_images', 'public');
            $data['room_image'] = $imagePath;
        }

        // Update the room
        $room->update($data);

        // Redirect back with success message
        return redirect()->route('room.index_page')->with('success', 'Room updated successfully');
    }

    public function archive(Request $request, Room $room)
    {
        // Validate input (optional if you just toggle archive)
        $data = $request->validate([
            'is_archived' => 'required|boolean',
        ]);

        // Update the archive status
        $service->update([
            'is_archived' => $data['is_archived'],
        ]);

        return redirect()->route('room.index_page')->with('success', 'Room archived successfully');
    }

    public function destroy(Room $room)
    {
        // Delete the room
        $room->delete();

        return redirect()->route('room.index_page')->with('success', 'Room deleted successfully');
    }  
}
