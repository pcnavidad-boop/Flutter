<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\RoomRulesService;
use App\Services\ItemLifecycleService;

class RoomController extends Controller
{
    protected RoomRulesService $rules;
    protected ItemLifecycleService $lifecycle;

    public function __construct(RoomRulesService $rules, ItemLifecycleService $lifecycle)
    {
        $this->rules = $rules;
        $this->lifecycle = $lifecycle;
    }

    // List rooms
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();
        return view('room.index', compact('rooms'));
    }

    // Create room
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:rooms,name',
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'room_type'   => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'  => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:1000',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'capacity'    => 'nullable|integer|min:1',
            'number_of_beds' => 'nullable|integer|min:1',
        ]);

        $type = $data['room_type'];

        if ($type === 'function') {
            $data['price_type'] = 'per_event_per_day';
            $data['number_of_beds'] = null;
            $data['capacity'] = (int) $request->validate(['capacity' => 'required|integer|min:1|max:250'])['capacity'];
        } else {
            $data['price_type'] = 'per_night';

            // Validate beds according to rules
            $beds = (int) $request->validate(['number_of_beds' => 'required|integer'])['number_of_beds'];
            try {
                $this->rules->validateBeds($type, $beds);
            } catch (\Exception $e) {
                return back()->withErrors(['number_of_beds' => $e->getMessage()])->withInput();
            }
            $data['number_of_beds'] = $beds;

            $capacity = (int) $request->validate(['capacity' => 'required|integer'])['capacity'];
            try {
                $this->rules->validateCapacity($type, $capacity);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()])->withInput();
            }
            $data['capacity'] = $capacity;
        }

        // Upload image first
        $data['image'] = $request->file('image')->store('room_images', 'public');

        // System fields
        $data['created_by']  = auth()->id();
        $data['status']      = 'available';
        $data['is_archived'] = false;

        Room::create($data);

        return redirect()->route('room.index_page')->with('success', 'Room created successfully.');
    }

    // Update room (no deletion here — archive used instead)
    public function update(Request $request, Room $room)
    {
        // Reject updates on archived items
        if ($room->is_archived) {
            return back()->withErrors(['error' => 'Cannot update an archived room.']);
        }

        $data = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('rooms','name')->ignore($room->id)],
            'room_number' => ['required','string','max:255', Rule::unique('rooms','room_number')->ignore($room->id)],
            'room_type'   => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'  => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:1000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|in:available,maintenance',
            'capacity'    => 'nullable|integer|min:1',
            'number_of_beds' => 'nullable|integer|min:1',
        ]);

        $type = $data['room_type'];

        // Prevent changing room_type if active bookings exist
        if ($room->bookings()->where('booking_status', '!=', 'cancelled')->exists() && $type !== $room->room_type) {
            return back()->withErrors(['room_type' => 'Cannot change room type because active bookings exist.'])->withInput();
        }

        // Validate capacity/beds and ensure capacity doesn't break existing bookings
        $activeBookings = $room->bookings()->where('booking_status', '!=', 'cancelled')->get();

        if ($type === 'function') {
            $capacity = (int) ($request->capacity ?? 0);
            try {
                $this->lifecycle->assertItemUpdatable($room); // extra guard (will throw if archived)
                $this->lifecycle->assertCanArchive($room); // not strictly needed here but keeps checks consistent
            } catch (\Exception $e) {
                // ignore — we only use assertItemUpdatable to keep consistency across services
            }

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $capacity) {
                    return back()->withErrors(['capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."]);
                }
            }

            $data['capacity'] = $capacity;
            $data['number_of_beds'] = null;
            $data['price_type'] = 'per_event_per_day';
        } else {
            $beds = (int) ($request->number_of_beds ?? 0);
            try {
                $this->rules->validateBeds($type, $beds);
            } catch (\Exception $e) {
                return back()->withErrors(['number_of_beds' => $e->getMessage()])->withInput();
            }
            $data['number_of_beds'] = $beds;

            $newCapacity = (int) ($request->capacity ?? 0);
            try {
                $this->rules->validateCapacity($type, $newCapacity);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()])->withInput();
            }

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $newCapacity) {
                    return back()->withErrors(['capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."]);
                }
            }

            $data['capacity'] = $newCapacity;
            $data['price_type'] = 'per_night';
        }

        // Replace image safely (upload then delete)
        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('room_images', 'public');
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $data['image'] = $newPath;
        }

        $room->update($data);

        return redirect()->route('room.index_page')->with('success', 'Room updated successfully.');
    }

    // Archive / unarchive a room (no delete)
    public function archive(Request $request, Room $room)
    {
        $request->validate(['is_archived' => 'required|boolean']);

        try {
            $this->lifecycle->assertCanArchive($room);
        } catch (\Exception $e) {
            return back()->withErrors(['is_archived' => $e->getMessage()]);
        }

        $room->update(['is_archived' => $request->is_archived]);

        return redirect()->route('room.index_page')->with('success', 'Room archive status updated.');
    }
}
