@extends('layouts.rooms')

@section('content')

<!-- DataTables CSS (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<x-alert_message></x-alert_message>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Rooms</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoomModal">
            + Add Room
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            
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
<x-modal.create_room />

<!-- edit modal -->
<x-modal.edit_room />

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
