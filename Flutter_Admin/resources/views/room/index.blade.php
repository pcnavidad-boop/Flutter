@extends('layouts.rooms')

@section('content')

<!-- DataTables CSS (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<!-- messages -->

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Rooms</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoomModal">
            + Add Room
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Add id for DataTables -->
            <table id="rooms-table" class="table table-striped align-middle display nowrap" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>Price/Night</th>
                        <th>Beds</th>
                        <th>Capacity</th>
                        <th>Availability</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                    <tr>
                        <td>{{ $room->id }}</td>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->room_type ?? 'N/A' }}</td>
                        <td>₱{{ number_format($room->price_per_night, 2) }}</td>
                        <td>{{ $room->number_of_beds ?? 'N/A' }}</td>
                        <td>{{ $room->room_capacity }}</td>
                        <td>
                            @if ($room->room_availability_status)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-danger">Unavailable</span>
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($room->room_description, 40, '...') }}</td>
                        <td>
                            <button 
                                class="btn btn-sm btn-outline-secondary editBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editRoomModal"
                                data-id="{{ $room->id }}"
                                data-room_number="{{ $room->room_number }}"
                                data-type="{{ $room->room_type }}"
                                data-price="{{ $room->price_per_night }}"
                                data-beds="{{ $room->number_of_beds }}"
                                data-capacity="{{ $room->room_capacity }}"
                                data-availability="{{ $room->room_availability_status }}"
                                data-description="{{ $room->room_description }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No rooms found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- create modal -->
<div class="modal fade" id="createRoomModal" tabindex="-1" aria-labelledby="createRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('room.store_data') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createRoomModalLabel">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Room Number</label>
                    <input type="text" name="room_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Room Type</label>
                    <input type="text" name="room_type" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Price per Night</label>
                    <input type="number" name="price_per_night" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Number of beds</label>
                    <input type="number" name="number_of_beds" step="1" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label>Room Capacity</label>
                    <input type="number" name="room_capacity" step="1" class="form-control" required>
                </div>                
                
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="room_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- edit modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editRoomForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="editModalAlerts"></div>

                <div class="mb-3">
                    <label>Room Number</label>
                    <input type="text" id="edit_room_number" name="room_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Room Type</label>
                    <input type="text" id="edit_room_type" name="room_type" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Price per Night</label>
                    <input type="number" name="price_per_night" id="edit_price_per_night" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Number of beds</label>
                    <input type="number" name="number_of_beds" id="edit_number_of_beds" step="1" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Room Capacity</label>
                    <input type="number" name="room_capacity" id="edit_room_capacity" step="1" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Availability</label>
                    <select name="room_availability_status" id="edit_room_availability" class="form-select">
                        <option value="1">Available</option>
                        <option value="0">Unavailable</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="room_description" id="edit_room_description" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">dsadaddsadsadasdwadsawdsUpdate</button>
            </div>
        </form>
    </div>
</div>

{{-- JS to populate edit modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const editForm = document.getElementById('editRoomForm');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            editForm.action = `/rooms/${id}`;

            document.getElementById('edit_room_number').value = button.dataset.room_number ?? '';
            document.getElementById('edit_room_type').value = button.dataset.type ?? '';
            document.getElementById('edit_price_per_night').value = button.dataset.price ?? '';
            document.getElementById('edit_number_of_beds').value = button.dataset.beds ?? '';
            document.getElementById('edit_room_capacity').value = button.dataset.capacity ?? '';
     
            const avail = (typeof button.dataset.availability !== 'undefined') ? button.dataset.availability : (button.dataset.room_availability || '0');
            document.getElementById('edit_room_availability').value = avail ? String(Number(avail)) : '0';
            document.getElementById('edit_room_description').value = button.dataset.description ?? '';
        });
    });

    const editModalEl = document.getElementById('editRoomModal');
    editModalEl.addEventListener('hidden.bs.modal', function () {
        editForm.reset();
        document.getElementById('editModalAlerts').innerHTML = '';
    });
});
</script>

<!-- jQuery + DataTables JS (CDN) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- DataTables initialization -->
<script>
$(document).ready(function() {
    $('#rooms-table').DataTable({
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthMenu: [ [10, 25, 50], [10, 25, 50] ],
        columnDefs: [
            { orderable: false, targets: -1 } // disable ordering on last column (Actions)
        ]
    });

    // Optional: re-init tooltips (if you use them in actions)
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

@endsection
