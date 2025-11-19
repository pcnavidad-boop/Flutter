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
                        <th>Price Type</th>
                        <th>Base Price</th>
                        <th>Time-Based?</th>
                        <th>Beds</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Archived?</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rooms as $room)
                    <tr>
                        <td>{{ $room->id }}</td>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->type }}</td>
                        <td>{{ ucfirst(str_replace('_',' ', $room->price_type)) }}</td>
                        <td>₱{{ number_format($room->base_price, 2) }}</td>
                        <td>
                            @if($room->is_time_based)
                                <span class="badge bg-info">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
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

                        <td>{{ \Illuminate\Support\Str::limit($room->description, 40, '...') }}</td>

                        <td>
                            @if ($room->is_archived)
                                <span class="badge bg-danger">Archived</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>

                        <td>
                            <button 
                                class="btn btn-sm btn-outline-secondary editBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editRoomModal"
                                data-id="{{ $room->id }}"
                                data-room_number="{{ $room->room_number }}"
                                data-type="{{ $room->type }}"
                                data-price_type="{{ $room->price_type }}"
                                data-price="{{ $room->base_price }}"
                                data-is_time_based="{{ $room->is_time_based }}"
                                data-beds="{{ $room->number_of_beds }}"
                                data-capacity="{{ $room->capacity }}"
                                data-status="{{ $room->status }}"
                                data-description="{{ $room->description }}">
                                Edit
                            </button>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">No rooms found.</td>
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

            editForm.action = `/rooms/${button.dataset.id}`;

            document.getElementById('edit_room_number').value = button.dataset.room_number ?? '';
            document.getElementById('edit_type').value = button.dataset.type ?? '';
            document.getElementById('edit_price_type').value = button.dataset.price_type ?? '';
            document.getElementById('edit_base_price').value = button.dataset.price ?? '';
            document.getElementById('edit_is_time_based').checked = (button.dataset.is_time_based == '1');
            document.getElementById('edit_number_of_beds').value = button.dataset.beds ?? '';
            document.getElementById('edit_capacity').value = button.dataset.capacity ?? '';
            document.getElementById('edit_status').value = button.dataset.status ?? '';
            document.getElementById('edit_description').value = button.dataset.description ?? '';
        });
    });

    const editModalEl = document.getElementById('editRoomModal');
    editModalEl.addEventListener('hidden.bs.modal', function () {
        editForm.reset();
    });
});
</script>

<!-- jQuery + DataTables JS (CDN) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

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
            { orderable: false, targets: -1 }
        ]
    });
});
</script>

@endsection
