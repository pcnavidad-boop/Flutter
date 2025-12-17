<x-admin.layout title="Rooms">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Rooms</h2>

    <!-- SEARCH + FILTER BAR CARD -->
    <div class="content-card mb-4">

        <form method="GET"
              action="{{ route('admin.rooms.index') }}"
              class="filter-bar d-flex justify-content-between align-items-center flex-wrap">

            <!-- LEFT FILTERS -->
            <div class="filter-controls d-flex gap-2 flex-wrap">

                <!-- SEARCH -->
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control rounded-pill"
                       placeholder="Search rooms...">

                <!-- TYPE -->
                <select name="type" class="form-select rounded-pill">
                    <option value="">All Types</option>
                    @foreach(['single','double','quad','family','suite','penthouse','function'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>

                <!-- STATUS -->
                <select name="status" class="form-select rounded-pill">
                    <option value="">All Status</option>
                    <option value="available" @selected(request('status')==='available')>Available</option>
                    <option value="maintenance" @selected(request('status')==='maintenance')>Maintenance</option>
                </select>

                <!-- ARCHIVED -->
                <select name="archived" class="form-select rounded-pill">
                    <option value="">Archived?</option>
                    <option value="0" @selected(request('archived')==='0')>Active</option>
                    <option value="1" @selected(request('archived')==='1')>Archived</option>
                </select>

                <!-- FILTER BUTTON -->
                <button class="btn btn-outline-coffee rounded-pill px-4">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <!-- RESET -->
                <a href="{{ route('admin.rooms.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>
            </div>

            <!-- RIGHT BUTTON -->
            <button class="btn btn-coffee rounded-pill px-4"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAddRoom">
                <i class="bi bi-plus-lg me-1"></i> Add Room
            </button>

        </form>
    </div>

    <!-- TABLE -->
    <div class="content-card">

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Room #</th>
                    <th>Type</th>
                    <th>Base Price</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Archived</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ ucfirst($room->room_type) }}</td>

                        <td>
                            ₱{{ $room->formatted_base_price }}
                            <small class="text-muted">{{ $room->formatted_price_type }}</small>
                        </td>

                        <td>{{ $room->capacity }}</td>

                        <td>
                            <span class="badge bg-{{ $room->status_badge }}">
                                {{ ucfirst($room->status) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $room->is_archived ? 'bg-secondary' : 'bg-success' }}">
                                {{ $room->is_archived ? 'Archived' : 'Active' }}
                            </span>
                        </td>

                        <td class="text-end">

                            <!-- VIEW -->
                            <button class="btn btn-sm btn-primary"
                                    data-id="{{ $room->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalViewRoom">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- EDIT -->
                            <button class="btn btn-sm btn-warning"
                                    data-id="{{ $room->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditRoom"
                                    {{ $room->is_archived ? 'disabled' : '' }}>
                                <i class="bi bi-pencil"></i>
                            </button>

                            <!-- ARCHIVE -->
                            <button class="btn btn-sm btn-danger"
                                    data-id="{{ $room->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalArchiveRoom">
                                <i class="bi bi-box-arrow-down"></i>
                            </button>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"
                            class="text-center py-4 text-muted"
                            style="font-size:1.1rem;">
                            <i class="bi bi-info-circle me-1"></i> No rooms found.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $rooms->withQueryString()->links('pagination::bootstrap-5') }}
        </div>

    </div>

    {{-- MODALS --}}
    @include('admin.rooms.modals.add')
    @include('admin.rooms.modals.edit')
    @include('admin.rooms.modals.archive')
    @include('admin.rooms.modals.view')

    {{-- AUTO-OPEN ADD MODAL IF VALIDATION FAILED --}}
    @if ($errors->addRoom->any())
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            new bootstrap.Modal(document.getElementById('modalAddRoom')).show();
        });
    </script>
    @endif
    
    {{-- AUTO-OPEN EDIT MODAL IF VALIDATION FAILED --}}
    @if ($errors->editRoom->any() && session('edit_id'))
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const id = "{{ session('edit_id') }}";

        // Re-open modal
        const modal = new bootstrap.Modal(document.getElementById('modalEditRoom'));

        // Fetch room details again
        fetch(`/admin/rooms/${id}`)
            .then(res => res.json())
            .then(room => {

                // Set form action
                document.querySelector('#formEditRoom').action = `/admin/rooms/${id}`;

                // Fill fields with old() or fallback to DB values
                document.querySelector('#edit-name').value = "{{ old('name') ?? '' }}" || room.name;
                document.querySelector('#edit-room-number').value = "{{ old('room_number') ?? '' }}" || room.room_number;
                document.querySelector('#edit-room-type').value = "{{ old('room_type') ?? '' }}" || room.room_type;
                document.querySelector('#edit-status').value = "{{ old('status') ?? '' }}" || room.status;
                document.querySelector('#edit-number-of-beds').value = "{{ old('number_of_beds') ?? '' }}" || room.beds ?? 0;
                document.querySelector('#edit-capacity').value = "{{ old('capacity') ?? '' }}" || room.capacity;
                document.querySelector('#edit-base-price').value = "{{ old('base_price') ?? '' }}" || room.base_price;
                document.querySelector('#edit-description').value = `{{ old('description') ?? '' }}` || (room.description ?? "");

                const priceRaw = "{{ old('price_type') ?? '' }}" || room.price_type;

                // Correct pricing logic
                document.querySelector('#edit-price-type-raw').value = priceRaw;
                document.querySelector('#edit-price-type-display').value =
                    priceRaw === 'per_day' ? 'per day' : 'per night';

                modal.show();
            });
    });
    </script>
    @endif

    @push('scripts')
        <script src="/admin/js/pages/rooms/common.js" defer></script>
        <script src="/admin/js/pages/rooms/add.js" defer></script>
        <script src="/admin/js/pages/rooms/edit.js" defer></script>
        <script src="/admin/js/pages/rooms/view.js" defer></script>
        <script src="/admin/js/pages/rooms/archive.js" defer></script>
    @endpush

</x-admin.layout>
