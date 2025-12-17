<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

    // Show room details (JSON for modal)
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

            // RAW VALUES
            'price_type'  => $room->price_type,
            'base_price'  => $room->base_price,

            // FORMATTED VALUES
            'formatted_price_type' => $room->formatted_price_type,
            'formatted_base_price' => $room->formatted_base_price,
            'price_label'          => $room->price_label,

            'status'      => $room->status,
            'is_archived' => $room->is_archived,
            'image_url'   => $room->image ? asset('storage/' . $room->image) : null,
            'created_at'  => $room->created_at->format('M d, Y h:i A'),
        ]);
    }

    // Create a new room
    public function store(Request $request)
    {
        // validation rules (single pass)
        $rules = [
            'name'            => 'required|string|max:255|unique:rooms,name',
            'room_number'     => 'required|string|max:255|unique:rooms,room_number',
            'room_type'       => 'required|in:single,double,quad,family,suite,penthouse,function',
            'base_price'      => 'required|numeric|min:0|max:999999.99',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'capacity'        => 'nullable|integer|min:1|max:500',
            'number_of_beds'  => 'nullable|integer|min:0|max:4',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // send errors to the named bag used by the add modal
            return back()->withErrors($validator->errors(), 'addRoom')->withInput();
        }

        $data = $validator->validated();

        $type = $data['room_type'];

        // Branching rules for function rooms vs overnight rooms
        if ($type === 'function') {
            // capacity required for function rooms
            if (empty($data['capacity'])) {
                return back()->withErrors(['capacity' => 'Capacity is required for function rooms.'], 'addRoom')->withInput();
            }
            $data['price_type'] = 'per_day';
            $data['number_of_beds'] = null;

            // validate capacity against rule service (throws Exception on invalid)
            try {
                $this->rules->validateCapacity($type, (int)$data['capacity']);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()], 'addRoom')->withInput();
            }

        } else {
            // number_of_beds and capacity required for non-function rooms
            if (!isset($data['number_of_beds']) || $data['number_of_beds'] === null) {
                return back()->withErrors(['number_of_beds' => 'Number of beds is required for this room type.'], 'addRoom')->withInput();
            }
            if (empty($data['capacity'])) {
                return back()->withErrors(['capacity' => 'Capacity is required for this room type.'], 'addRoom')->withInput();
            }

            try {
                $this->rules->validateBeds($type, (int)$data['number_of_beds']);
            } catch (\Exception $e) {
                return back()->withErrors(['number_of_beds' => $e->getMessage()], 'addRoom')->withInput();
            }

            try {
                $this->rules->validateCapacity($type, (int)$data['capacity']);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()], 'addRoom')->withInput();
            }

            $data['price_type'] = 'per_night';
        }

        // store image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('room_images', 'public');
        }

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

        // VALIDATION WRAPPED TO USE editRoom BAG
        try {
            $data = $request->validate([
                'name'            => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room->id)],
                'room_number'     => ['required', 'string', 'max:255', Rule::unique('rooms', 'room_number')->ignore($room->id)],
                'room_type'       => 'required|in:single,double,quad,family,suite,penthouse,function',
                'base_price'      => 'required|numeric|min:0|max:999999.99',
                'description'     => 'nullable|string|max:1000',
                'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status'          => 'required|in:available,maintenance',
                'capacity'        => 'nullable|integer|min:1|max:500',
                'number_of_beds'  => 'nullable|integer|min:0|max:4',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return back()
                ->withErrors($e->errors(), 'editRoom')
                ->withInput()
                ->with('edit_id', $room->id);
        }

        $type = $data['room_type'];
        $activeBookings = $room->bookings()->where('booking_status', '!=', 'cancelled')->get();

        // Cannot change type if active bookings exist
        if ($activeBookings->isNotEmpty() && $type !== $room->room_type) {
            return back()->withErrors(
                ['room_type' => 'Cannot change room type because active bookings exist.'],
                'editRoom'
            )->withInput()->with('edit_id', $room->id);
        }

        // FUNCTION ROOM LOGIC
        if ($type === 'function') {

            $capacity = (int) ($request->capacity ?? 0);

            // Prevent lowering capacity below existing bookings
            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $capacity) {
                    return back()->withErrors([
                        'capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ], 'editRoom')->withInput()->with('edit_id', $room->id);
                }
            }

            try {
                $this->rules->validateCapacity($type, $capacity);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()], 'editRoom')
                            ->withInput()->with('edit_id', $room->id);
            }

            $data['number_of_beds'] = null;
            $data['capacity'] = $capacity;
            $data['price_type'] = 'per_day';

        } else {

            $beds = (int) ($request->number_of_beds ?? 0);

            // Validate beds
            try {
                $this->rules->validateBeds($type, $beds);
            } catch (\Exception $e) {
                return back()->withErrors(['number_of_beds' => $e->getMessage()], 'editRoom')
                            ->withInput()->with('edit_id', $room->id);
            }

            $newCapacity = (int) ($request->capacity ?? 0);

            try {
                $this->rules->validateCapacity($type, $newCapacity);
            } catch (\Exception $e) {
                return back()->withErrors(['capacity' => $e->getMessage()], 'editRoom')
                            ->withInput()->with('edit_id', $room->id);
            }

            // Prevent lowering capacity below existing bookings
            foreach ($activeBookings as $b) {
                if ($b->number_of_guests > $newCapacity) {
                    return back()->withErrors([
                        'capacity' => "Cannot reduce capacity below existing booking of {$b->number_of_guests} guests."
                    ], 'editRoom')->withInput()->with('edit_id', $room->id);
                }
            }

            $data['number_of_beds'] = $beds;
            $data['capacity'] = $newCapacity;
            $data['price_type'] = 'per_night';
        }

        // IMAGE HANDLING
        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('room_images', 'public');
            Storage::disk('public')->delete($room->image);
            $data['image'] = $newImage;
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
