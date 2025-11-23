@extends('layouts.rooms')

@section('content')

<!-- DataTables CSS (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<x-alert_message></x-alert_message>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Room Bookings</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calendarFilterModal">
            + Add Booking
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="bookings-table" class="table table-striped align-middle display nowrap" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Event Date</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Processed By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->guest_email }}</td>
                        <td>{{ $booking->guest_contact ?? 'N/A' }}</td>
                        <td>{{ $booking->room->room_number ?? 'N/A' }}</td>
                        <td>{{ $booking->check_in_date ?? '-' }}</td>
                        <td>{{ $booking->check_out_date ?? '-' }}</td>
                        <td>{{ $booking->event_date ?? '-' }}</td>
                        <td>{{ $booking->booking_date }}</td>
                        <td>
                            @switch($booking->status)
                                @case('Pending')
                                    <span class="badge bg-warning text-dark">{{ $booking->status }}</span>
                                    @break
                                @case('Confirmed')
                                    <span class="badge bg-success">{{ $booking->status }}</span>
                                    @break
                                @case('Declined')
                                    <span class="badge bg-danger">{{ $booking->status }}</span>
                                    @break
                                @case('Checked_In')
                                    <span class="badge bg-primary">{{ $booking->status }}</span>
                                    @break
                                @case('Checked_Out')
                                    <span class="badge bg-secondary">{{ $booking->status }}</span>
                                    @break
                                @case('Cancelled')
                                    <span class="badge bg-dark">{{ $booking->status }}</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark">{{ $booking->status }}</span>
                            @endswitch
                        </td>
                        <td>{{ $booking->processedBy->name ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- jQuery + DataTables JS (CDN) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- DataTables initialization -->
<script>
$('#bookings-table').DataTable({
    responsive: true,
    paging: true,
    searching: true,
    ordering: true,
    pageLength: 10,
    lengthMenu: [ [10, 25, 50], [10, 25, 50] ],

    language: {
        emptyTable: "No bookings found."
    }
});

</script>

<x-modal.calendar_filter />


@if(session('error'))
<script>
    var myModal = new bootstrap.Modal(document.getElementById('calendarFilterModal'));
    myModal.show();
</script>
@endif



@endsection
