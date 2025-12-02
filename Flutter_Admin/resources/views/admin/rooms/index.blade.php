<x-admin.layout title="Rooms">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Rooms</h2>

    <!-- SEARCH + FILTER BAR CARD -->
    <div class="content-card mb-4">

        <form method="GET"
              action="{{ route('admin.rooms.index') }}"
              class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <!-- LEFT FILTERS -->
            <div class="d-flex align-items-center flex-wrap gap-2 filter-row">

                <!-- SEARCH -->
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control rounded-pill filter-input"
                       placeholder="Search rooms...">

                <!-- TYPE -->
                <select name="type" class="form-select rounded-pill filter-input">
                    <option value="">All Types</option>
                    <option value="single"     {{ request('type')=='single'?'selected':'' }}>Single</option>
                    <option value="double"     {{ request('type')=='double'?'selected':'' }}>Double</option>
                    <option value="quad"       {{ request('type')=='quad'?'selected':'' }}>Quad</option>
                    <option value="family"     {{ request('type')=='family'?'selected':'' }}>Family</option>
                    <option value="suite"      {{ request('type')=='suite'?'selected':'' }}>Suite</option>
                    <option value="penthouse"  {{ request('type')=='penthouse'?'selected':'' }}>Penthouse</option>
                    <option value="function"   {{ request('type')=='function'?'selected':'' }}>Function Room</option>
                </select>

                <!-- STATUS -->
                <select name="status" class="form-select rounded-pill filter-input">
                    <option value="">All Status</option>
                    <option value="available"   {{ request('status')=='available'?'selected':'' }}>Available</option>
                    <option value="maintenance" {{ request('status')=='maintenance'?'selected':'' }}>Maintenance</option>
                </select>

                <!-- ARCHIVED -->
                <select name="archived" class="form-select rounded-pill filter-input">
                    <option value="">Archived?</option>
                    <option value="0" {{ request('archived')=='0'?'selected':'' }}>Active</option>
                    <option value="1" {{ request('archived')=='1'?'selected':'' }}>Archived</option>
                </select>

                <!-- FILTER -->
                <button class="btn btn-outline-coffee rounded-pill px-4" type="submit">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <!-- RESET -->
                <a href="{{ route('admin.rooms.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>
            </div>

            <!-- ADD ROOM -->
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
                    <th>Room Number</th>
                    <th>Type</th>
                    <th>Base Price</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Archived</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>

                <tbody>
                @if ($rooms->count() === 0)
                    <tr>
                        <td colspan="8"
                            class="text-center py-4 text-muted"
                            style="font-size:1.1rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            No rooms found.
                        </td>
                    </tr>
                @else
                    @foreach ($rooms as $room)
                        <tr>
                            <td>{{ $room->name }}</td>
                            <td>{{ $room->room_number }}</td>
                            <td>{{ ucfirst($room->room_type) }}</td>

                            <!-- CLEAN MODEL–DRIVEN PRICE -->
                            <td>
                                ₱{{ $room->formatted_base_price }}
                                <small class="text-muted">{{ $room->formatted_price_type }}</small>
                            </td>

                            <td>{{ $room->capacity }}</td>

                            <!-- STATUS -->
                            <td>
                                @if ($room->status === 'available')
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-warning text-dark">Maintenance</span>
                                @endif
                            </td>

                            <!-- ARCHIVED -->
                            <td>
                                @if ($room->is_archived)
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
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
                                @if (!$room->is_archived)
                                    <button class="btn btn-sm btn-warning"
                                            data-id="{{ $room->id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditRoom">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif

                                <!-- ARCHIVE -->
                                <button class="btn btn-sm btn-danger"
                                        data-id="{{ $room->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalArchiveRoom">
                                    <i class="bi bi-box-arrow-down"></i>
                                </button>

                            </td>
                        </tr>
                    @endforeach
                @endif

                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="d-flex justify-content-end mt-3">
            {{ $rooms->withQueryString()->links('pagination::bootstrap-5') }}
        </div>

    </div>


    {{-- MODALS --}}
    @include('admin.rooms.modals.add')
    @include('admin.rooms.modals.edit')
    @include('admin.rooms.modals.archive')
    @include('admin.rooms.modals.view')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ROOM RULES (same as backend) */
    const bedRules = {
        single:[1,1], double:[1,2], quad:[2,2],
        family:[2,3], suite:[1,2], penthouse:[2,4],
        function:[0,0]
    };

    const capacityRules = {
        single:[1,1], double:[1,2], quad:[4,4],
        family:[4,6], suite:[2,4], penthouse:[4,8],
        function:[1,10000]
    };

    /* PRICE TYPE MAP (pure readable output) */
    function getPriceType(type) {
        const map = {
            single: "per night",
            double: "per night",
            quad: "per night",
            family: "per night",
            suite: "per night",
            penthouse: "per night",
            function: "per day"
        };
        return map[type] ?? "per night";
    }

    /* HINT UPDATER */
    function updateHints(prefix, type) {
        const bedsBlock = document.getElementById(`${prefix}-beds-block`);
        const bedsHint = document.getElementById(`${prefix}-beds-hint`);
        const capHint = document.getElementById(`${prefix}-capacity-hint`);

        if (!type) return;

        if (type === 'function') {
            if (bedsBlock) bedsBlock.style.display = 'none';
        } else {
            if (bedsBlock) bedsBlock.style.display = 'block';
        }

        if (bedsHint) {
            const [bMin,bMax] = bedRules[type];
            bedsHint.innerText = type === 'function' ? "" : `Allowed: ${bMin}–${bMax} beds`;
        }

        if (capHint) {
            const [cMin,cMax] = capacityRules[type];
            capHint.innerText = `Allowed: ${cMin}–${cMax} capacity`;
        }
    }

    /* ADD MODAL */
    const addType = document.getElementById('add-room-type');
    const addPT = document.querySelector('[name="price_type"]');

    if (addType) {
        addType.addEventListener('change', e => {
            updateHints('add', e.target.value);
            addPT.value = getPriceType(e.target.value);

            // function room auto-beds
            const bedsBlock = document.getElementById('add-beds-block');
            const bedsInput = document.getElementById('add-number-of-beds');
            const bedsHint = document.getElementById('add-beds-hint');

            if (e.target.value === 'function') {
                if (bedsBlock) bedsBlock.style.display = 'none';
                if (bedsInput) bedsInput.value = 0;
                if (bedsHint) bedsHint.innerText = '';
            } else {
                if (bedsBlock) bedsBlock.style.display = 'block';
                if (bedsInput && !bedsInput.value) bedsInput.value = 1;
            }
        });

        // initialize on page load
        updateHints('add', addType.value);
        addPT.value = getPriceType(addType.value);

        // ensure default value for beds exists to avoid null submit
        const addBedsInput = document.getElementById('add-number-of-beds');
        if (addBedsInput && !addBedsInput.value) addBedsInput.value = (addType.value === 'function' ? 0 : 1);
    }

    /* VIEW MODAL */
    document.getElementById('modalViewRoom').addEventListener('show.bs.modal', event => {
        const id = event.relatedTarget.getAttribute('data-id');

        fetch(`/admin/rooms/${id}`)
            .then(r => r.json())
            .then(room => {

                document.getElementById('viewRoomImage').src = room.image_url;
                document.getElementById('viewRoomName').innerText = room.name;
                document.getElementById('viewRoomNumber').innerText = room.room_number;
                document.getElementById('viewRoomType').innerText =
                    room.room_type.charAt(0).toUpperCase() + room.room_type.slice(1);

                document.getElementById('viewRoomPrice').innerText = room.base_price;
                document.getElementById('viewRoomPriceType').innerText = room.price_type;

                document.getElementById('viewRoomCapacity').innerText = room.capacity;
                document.getElementById('viewRoomBeds').innerText = room.beds ?? '';
                document.getElementById('viewRoomDescription').innerText = room.description ?? "";
            });
    });

    /* EDIT MODAL */
    document.getElementById('modalEditRoom').addEventListener('show.bs.modal', event => {

        const id = event.relatedTarget.getAttribute('data-id');
        const form = document.getElementById('formEditRoom');
        form.action = `/admin/rooms/${id}`;

        fetch(`/admin/rooms/${id}`)
            .then(r => r.json())
            .then(room => {

                document.getElementById('edit-name').value = room.name;
                document.getElementById('edit-room-number').value = room.room_number;
                document.getElementById('edit-room-type').value = room.room_type;
                document.getElementById('edit-capacity').value = room.capacity;
                document.getElementById('edit-number-of-beds').value = room.beds ?? '';
                document.getElementById('edit-base-price').value = room.base_price;
                document.getElementById('edit-description').value = room.description ?? '';
                document.getElementById('edit-status').value = room.status;

                document.getElementById('edit-price-type').value = room.price_type;

                updateHints('edit', room.room_type);

                // enforce function room behaviour if needed
                const editBedsBlock = document.getElementById('edit-beds-block');
                const editBedsInput = document.getElementById('edit-number-of-beds');
                const editBedsHint = document.getElementById('edit-beds-hint');

                if (room.room_type === 'function') {
                    if (editBedsBlock) editBedsBlock.style.display = 'none';
                    if (editBedsInput) editBedsInput.value = 0;
                    if (editBedsHint) editBedsHint.innerText = '';
                } else {
                    if (editBedsBlock) editBedsBlock.style.display = 'block';
                }
            });
    });

    /* TYPE CHANGE IN EDIT MODAL */
    document.getElementById('edit-room-type').addEventListener('change', e => {
        updateHints('edit', e.target.value);
        document.getElementById('edit-price-type').value = getPriceType(e.target.value);

        // enforce function room behaviour
        const editBedsBlock = document.getElementById('edit-beds-block');
        const editBedsInput = document.getElementById('edit-number-of-beds');
        const editBedsHint = document.getElementById('edit-beds-hint');

        if (e.target.value === 'function') {
            if (editBedsBlock) editBedsBlock.style.display = 'none';
            if (editBedsInput) editBedsInput.value = 0;
            if (editBedsHint) editBedsHint.innerText = '';
        } else {
            if (editBedsBlock) editBedsBlock.style.display = 'block';
            if (editBedsInput && !editBedsInput.value) editBedsInput.value = 1;
        }
    });

    /* ARCHIVE MODAL */
    document.getElementById('modalArchiveRoom').addEventListener('show.bs.modal', event => {
        const id = event.relatedTarget.getAttribute('data-id');
        document.getElementById('formArchiveRoom').action = `/admin/rooms/${id}/archive`;

        fetch(`/admin/rooms/${id}`)
            .then(r => r.json())
            .then(room => {
                document.getElementById('archiveSelect').value = room.is_archived ? "0" : "1";
            });
    });

    /* AUTO-OPEN EDIT MODAL IF VALIDATION ERRORS */
    @if ($errors->hasBag('editRoom'))
        new bootstrap.Modal(document.getElementById('modalEditRoom')).show();
    @endif

});
</script>
@endpush

</x-admin.layout>
