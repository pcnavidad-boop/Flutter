<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    // List rooms with filters and pagination
    public function index(Request $request)
    {
        $query = Room::query()->orderBy('room_number');

        // Global Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('room_number', 'like', "%{$search}%")
                ->orWhere('room_type', 'like', "%{$search}%");
            });
        }

        // Filter by Type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('room_type', $request->type);
        }

        // Filter by Status (available / maintenance)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Archived (0 or 1)
        if ($request->filled('archived') && $request->archived !== 'all') {
            $query->where('is_archived', $request->archived);
        }

        // Pagination with filters retained
        $rooms = $query->paginate(10)->appends($request->query());

        return view('admin.rooms.index', compact('rooms'));
    }

    // Show room details
    public function show(Room $room)
    {
        return response()->json([
            'id'          => $room->id,
            'name'        => $room->name,
            'room_number' => $room->room_number,
            'room_type'   => $room->room_type,
            'description' => $room->description,
            'capacity'    => $room->capacity,
            'beds'        => $room->number_of_beds,
            'price_type'  => $room->formatted_price_type,
            'base_price'  => $room->formatted_base_price,
            'status'      => $room->status,
            'is_archived' => $room->is_archived,
            'image_url'   => asset('storage/' . $room->image),
            'created_at'  => $room->created_at->format('M d, Y h:i A'),
        ]);
    }

    // Create a new room
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255|unique:rooms,name',
            'room_number'     => 'required|string|max:255|unique:rooms,room_number',
            'room_type'       => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'      => 'required|numeric|min:0|max:999999.99',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'capacity'        => 'nullable|integer|min:1',
            'number_of_beds'  => 'nullable|integer|min:0',
        ]);

        $type = $data['room_type'];

        if ($type === 'function') {
            $data['price_type'] = 'per_event_per_day';
            $data['number_of_beds'] = null;

            $data['capacity'] = (int) $request->validate([
                'capacity' => 'required|integer|min:1|max:250'
            ])['capacity'];

        } else {
            $data['price_type'] = 'per_night';

            $beds = (int) $request->validate([
                'number_of_beds' => 'required|integer|min:1'
            ])['number_of_beds'];

            try {
                $this->rules->validateBeds($type, $beds);
            } catch (\Exception $e) {
                return back()
                    ->withErrors(['number_of_beds' => $e->getMessage()])
                    ->withInput();
            }

            $data['number_of_beds'] = $beds;

            $capacity = (int) $request->validate([
                'capacity' => 'required|integer|min:1'
            ])['capacity'];

            try {
                $this->rules->validateCapacity($type, $capacity);
            } catch (\Exception $e) {
                return back()
                    ->withErrors(['capacity' => $e->getMessage()])
                    ->withInput();
            }

            $data['capacity'] = $capacity;
        }

        $data['image'] = $request->file('image')->store('room_images', 'public');
        $data['created_by']  = auth()->id();
        $data['status']      = 'available';
        $data['is_archived'] = false;

        Room::create($data);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    // Update an existing room
    public function update(Request $request, Room $room)
    {
        if ($room->is_archived) {
            return back()->with('error', 'Cannot update an archived room.');
        }

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room->id)],
            'room_number'     => ['required', 'string', 'max:255', Rule::unique('rooms', 'room_number')->ignore($room->id)],
            'room_type'       => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'      => 'required|numeric|min:0|max:999999.99',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'          => 'required|in:available,maintenance',
            'capacity'        => 'nullable|integer|min:1',
            'number_of_beds'  => 'nullable|integer|min:0',
        ]);

        $type = $data['room_type'];
        $activeBookings = $room->bookings()->where('booking_status', '!=', 'cancelled')->get();

        if ($room->bookings()->where('booking_status', '!=', 'cancelled')->exists() &&
            $type !== $room->room_type) {
            return back()->withErrors(['room_type' => 'Cannot change room type because active bookings exist.']);
        }

        if ($type === 'function') {

            $capacity = (int) ($request->capacity ?? 0);

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $capacity) {
                    return back()->withErrors([
                        'capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ]);
                }
            }

            $data['number_of_beds'] = null;
            $data['capacity'] = $capacity;
            $data['price_type'] = 'per_event_per_day';

        } else {
            $beds = (int) ($request->number_of_beds ?? 0);

            try {
                $this->rules->validateBeds($type, $beds);
            } catch (\Exception $e) {
                return back()->withErrors(['number_of_beds' => $e->getMessage()]);
            }

            $newCapacity = (int) ($request->capacity ?? 0);

            try {
                $this->rules->validateCapacity($type, $newCapacity);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()]);
            }

            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $newCapacity) {
                    return back()->withErrors([
                        'capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ]);
                }
            }

            $data['number_of_beds'] = $beds;
            $data['capacity'] = $newCapacity;
            $data['price_type'] = 'per_night';
        }

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('room_images', 'public');
            Storage::disk('public')->delete($room->image);
            $data['image'] = $newPath;
        }

        $room->update($data);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    // Archive or unarchive a room
    public function archive(Request $request, Room $room)
    {
        $request->validate([
            'is_archived' => 'required|boolean',
        ]);

        try {
            $this->lifecycle->assertCanArchive($room);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $room->update([
            'is_archived' => $request->is_archived,
        ]);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Room archive status updated.');
    }
}
