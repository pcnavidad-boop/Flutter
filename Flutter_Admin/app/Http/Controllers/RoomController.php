<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    //not used
    public function viewCreatePage(){

        return view('room.create');
    }

    //view rooms and *services(in the future)*
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();

        //dd($rooms);
        //xxx.xxxx.xxxx means folder xxx folder xxxx file xxxx.blade.php
        return view('room.index', compact('rooms'));

    }

    public function butang(Request $request)
    {
        //validation

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

        $data['room_availability_status'] = true; //always true 

        


        //what to do with data
        Room::create($data);


        //package data to views file
        return redirect()->route('room.index_page')->with('success', 'Room created successfully');

    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            
            'room_number' => ['required','string','max:255',Rule::unique('rooms', 'room_number')->ignore($room->id),],
            'room_type' => 'nullable|string|max:255',
            'price_per_night' => 'required|numeric|min:0|max:99999999.99',
            'number_of_beds' => 'nullable|integer|min:1',
            'room_capacity' => 'required|integer|min:1',
            'room_description' => 'nullable|string|max:1000',
            'room_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ]);

        //do this for update boolean
        $data['room_availability_status'] = $request->has('room_availability_status')
            ? (int) $request->input('room_availability_status')
            : 0; 
        
        $room->update($data);

        return redirect()->route('room.index_page')->with('success', 'Room updated successfully');

    }

    public function archive(Request $request, Room $room)
    {
        $data = $request->validate([
            
            'is_archived' => 'required|string|max:255|unique:rooms,room_number',

        ]);

        Room::update($data);

        return redirect()->route('room.index_page')->with('success', 'Room archived successfully');

    }

    
    public function destroy(Room $room)
    {

        $room->delete();
        return redirect()->route('room.index_page')->with('success', 'Room deleted successfully');

    }




    
}
