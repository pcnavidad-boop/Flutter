@extends('layouts.rooms')

@section('content')

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
                        <th>Price Type</th>
                        <th>Base Price</th>
                        <th>Beds</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Archived</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->id }}</td>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->type }}</td>
                        <td>{{ $room->price_type }}</td>
                        <td>₱{{ number_format($room->base_price, 2) }}</td>
                        <td>{{ $room->number_of_beds ?? 'N/A' }}</td>
                        <td>{{ $room->capacity }}</td>

                        <td>
                            @if ($room->status === 'Available')
                                <span class="badge bg-success">Available</span>
                            @elseif ($room->status === 'Occupied')
                                <span class="badge bg-warning">Occupied</span>
                            @elseif ($room->status === 'Maintenance')
                                <span class="badge bg-info">Maintenance</span>
                            @else
                                <span class="badge bg-danger">Unavailable</span>
                            @endif
                        </td>

                        <td>
                            @if($room->is_archived)
                                <span class="badge bg-danger">Archived</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>

                        <td>{{ Str::limit($room->description, 40) }}</td>

                        <td>
                            <!-- VIEW BUTTON -->
                            <button 
                                class="btn btn-sm btn-outline-primary viewBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#viewRoomModal"
                                data-room="{{ htmlspecialchars(json_encode($room), ENT_QUOTES, 'UTF-8') }}"
                            >View</button>

                            <!-- EDIT BUTTON -->
                            <button 
                                class="btn btn-sm btn-outline-secondary editBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editRoomModal"
                                data-room="{{ htmlspecialchars(json_encode($room), ENT_QUOTES, 'UTF-8') }}"
                            >Edit</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>
</div>

<!-- --- MODALS --- -->
<x-modal.create_room />
<x-modal.edit_room />
<x-modal.view_room />

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
    $('#rooms-table').DataTable({
        responsive: true,
        pageLength: 10,
        ordering: true,
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
            emptyTable: "No rooms found.",
            zeroRecords: "No matching rooms found."
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // VIEW MODAL LOGIC
    document.querySelectorAll('.viewBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const room = JSON.parse(btn.dataset.room);

            document.getElementById('view_room_number').innerText = "Room " + room.room_number;
            document.getElementById('view_type').innerText = room.type;
            document.getElementById('view_price').innerText = "₱" + parseFloat(room.base_price).toLocaleString();
            document.getElementById('view_price_type').innerText = "(" + room.price_type.replace('_', ' ') + ")";
            document.getElementById('view_description').innerText = room.description ?? '';
            document.getElementById('view_status').innerText = room.status;
            document.getElementById('view_capacity').innerText = room.capacity + " guests";

            document.getElementById('view_image').src =
                room.image ? "/storage/" + room.image : "/images/default-room.jpg";
        });
    });

    // EDIT MODAL LOGIC
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {

            const room = JSON.parse(btn.dataset.room);
            const form = document.getElementById('editRoomForm');
            form.action = `/rooms/${room.id}`;

            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_type').value = room.type;
            document.getElementById('edit_price_type').value = room.price_type;
            document.getElementById('edit_base_price').value = room.base_price;
            document.getElementById('edit_number_of_beds').value = room.number_of_beds ?? '';
            document.getElementById('edit_capacity').value = room.capacity;
            document.getElementById('edit_status').value = room.status;
            document.getElementById('edit_description').value = room.description ?? '';
            document.getElementById('edit_is_archived').value = room.is_archived ? 1 : 0;
        });
    });

});
</script>

@endsection
