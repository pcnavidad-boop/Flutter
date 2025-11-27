<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RoomController extends Controller
{
    // View all rooms
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();
        return view('room.index', compact('rooms'));
    }

    // Create a new room
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:rooms,name',
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'room_type'   => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'  => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:1000',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $type = $request->room_type;

        // Function hall rules
        if ($type === 'function') {

            $data['price_type'] = 'per_event_per_day';
            $data['number_of_beds'] = null;

            $data['capacity'] = $request->validate([
                'capacity' => 'required|integer|min:1|max:250'
            ])['capacity'];

        } else {

            $data['price_type'] = 'per_night';

            $bedRules = [
                'single'    => ['min' => 1, 'max' => 1],
                'double'    => ['min' => 1, 'max' => 2],
                'quad'      => ['min' => 2, 'max' => 2],
                'family'    => ['min' => 2, 'max' => 3],
                'suite'     => ['min' => 1, 'max' => 2],
                'penthouse' => ['min' => 2, 'max' => 4],
            ];

            $request->validate([
                'number_of_beds' =>
                    "required|integer|min:{$bedRules[$type]['min']}|max:{$bedRules[$type]['max']}"
            ]);

            $data['number_of_beds'] = $request->number_of_beds;

            $capacityRules = [
                'single'    => ['min' => 1, 'max' => 1],
                'double'    => ['min' => 2, 'max' => 2],
                'quad'      => ['min' => 4, 'max' => 4],
                'family'    => ['min' => 4, 'max' => 6],
                'suite'     => ['min' => 2, 'max' => 4],
                'penthouse' => ['min' => 4, 'max' => 8],
            ];

            $request->validate([
                'capacity' =>
                    "required|integer|min:{$capacityRules[$type]['min']}|max:{$capacityRules[$type]['max']}"
            ]);

            $data['capacity'] = $request->capacity;
        }

        // Image upload FIRST
        $path = $request->file('image')->store('room_images', 'public');
        $data['image'] = $path;

        // System fields
        $data['created_by']  = auth()->id();
        $data['status']      = 'available';
        $data['is_archived'] = false;

        Room::create($data);

        return redirect()->route('room.index_page')
            ->with('success', 'Room created successfully.');
    }

    // Update a room
    public function update(Request $request, Room $room)
    {
        // ❗ Block editing archived rooms
        if ($room->is_archived) {
            return back()->withErrors([
                'error' => 'Cannot update an archived room.'
            ]);
        }

        $data = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('rooms','name')->ignore($room->id)],
            'room_number' => ['required','string','max:255', Rule::unique('rooms','room_number')->ignore($room->id)],
            'room_type'   => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'  => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:1000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|in:available,maintenance',
        ]);

        $type = $request->room_type;

        // Prevent changing room type if active bookings exist
        if ($room->bookings()->where('booking_status','!=','cancelled')->exists()) {
            if ($type !== $room->room_type) {
                return back()->withErrors([
                    'room_type' => 'Cannot change room type because active bookings exist.'
                ])->withInput();
            }
        }

        // ❗ Prevent lowering capacity below existing bookings
        $activeBookings = $room->bookings()->where('booking_status','!=','cancelled')->get();

        // Function hall logic
        if ($type === 'function') {

            $capacity = $request->validate([
                'capacity' => 'required|integer|min:1|max:250'
            ])['capacity'];

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $capacity) {
                    return back()->withErrors([
                        'capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ]);
                }
            }

            $data['capacity'] = $capacity;
            $data['number_of_beds'] = null;
            $data['price_type'] = 'per_event_per_day';

        } else {

            $bedRules = [
                'single'    => ['min' => 1, 'max' => 1],
                'double'    => ['min' => 1, 'max' => 2],
                'quad'      => ['min' => 2, 'max' => 2],
                'family'    => ['min' => 2, 'max' => 3],
                'suite'     => ['min' => 1, 'max' => 2],
                'penthouse' => ['min' => 2, 'max' => 4],
            ];

            $request->validate([
                'number_of_beds' =>
                    "required|integer|min:{$bedRules[$type]['min']}|max:{$bedRules[$type]['max']}"
            ]);

            $data['number_of_beds'] = $request->number_of_beds;

            $capacityRules = [
                'single'    => ['min' => 1, 'max' => 1],
                'double'    => ['min' => 2, 'max' => 2],
                'quad'      => ['min' => 4, 'max' => 4],
                'family'    => ['min' => 4, 'max' => 6],
                'suite'     => ['min' => 2, 'max' => 4],
                'penthouse' => ['min' => 4, 'max' => 8],
            ];

            $request->validate([
                'capacity' =>
                    "required|integer|min:{$capacityRules[$type]['min']}|max:{$capacityRules[$type]['max']}"
            ]);

            $newCapacity = $request->capacity;

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $newCapacity) {
                    return back()->withErrors([
                        'capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ]);
                }
            }

            $data['capacity'] = $newCapacity;
            $data['price_type'] = 'per_night';
        }

        // Image replacement (upload first)
        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('room_images', 'public');

            // delete old only after successful upload
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }

            $data['image'] = $newPath;
        }

        $room->update($data);

        return redirect()->route('room.index_page')
            ->with('success', 'Room updated successfully.');
    }

    // Archive / Unarchive
    public function archive(Request $request, Room $room)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        // Prevent archiving if there are future bookings (stay or event)
        $hasFutureBooking = $room->bookings()
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('start_date', '>=', today())
            ->exists();

        if ($hasFutureBooking) {
            return back()->withErrors([
                'is_archived' => 'Cannot archive this room because future bookings exist.'
            ]);
        }

        $room->update([
            'is_archived' => $request->is_archived
        ]);

        return redirect()->route('room.index_page')
            ->with('success', 'Room archive status updated.');
    }

    // Delete a room
    public function destroy(Room $room)
    {
        // Cannot delete archived rooms
        if ($room->is_archived === true) {
            return back()->withErrors([
                'error' => 'Cannot delete archived rooms. Unarchive then archive bookings first.'
            ]);
        }

        if ($room->bookings()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete this room because bookings exist. Archive it instead.'
            ]);
        }

        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()->route('room.index_page')
            ->with('success', 'Room deleted successfully.');
    }
}
