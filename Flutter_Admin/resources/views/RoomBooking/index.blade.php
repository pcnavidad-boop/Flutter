@extends('layouts.rooms')

@section('content')

<!-- DataTables CSS (Bootstrap 5) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

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
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th>Action</th>
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
                        <td>{{ $booking->booking_date }}</td>
                        <td>
                            @switch($booking->booking_status)
                                @case('Pending')
                                    <span class="badge bg-warning text-dark">{{ $booking->booking_status }}</span>
                                    @break
                                @case('Confirmed')
                                    <span class="badge bg-success">{{ $booking->booking_status }}</span>
                                    @break
                                @case('Declined')
                                    <span class="badge bg-danger">{{ $booking->booking_status }}</span>
                                    @break
                                @case('Checked_In')
                                    <span class="badge bg-primary">{{ $booking->booking_status }}</span>
                                    @break
                                @case('Checked_Out')
                                    <span class="badge bg-secondary">{{ $booking->booking_status }}</span>
                                    @break
                                @case('Cancelled')
                                    <span class="badge bg-dark">{{ $booking->booking_status }}</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark">{{ $booking->booking_status }}</span>
                            @endswitch
                        </td>
                        <td>{{ $booking->user->name ?? 'N/A' }}</td>
                        <td>
                                <!-- VIEW BUTTON -->
                            <button 
                            
                                class="btn btn-sm btn-outline-secondary viewBookingBtn"
       

                                data-guest-name="{{ $booking->guest_name }}"
                                data-guest-email="{{ $booking->guest_email }}"
                                data-guest-contact="{{ $booking->guest_contact }}"

                                data-room="{{ $booking->room->room_number }}"
                                data-booking-date="{{ $booking->booking_date }}"

                                data-check-in="{{ $booking->check_in_date }}"
                                data-check-out="{{ $booking->check_out_date }}"
                                data-start-time="{{ $booking->start_time }}"
                                data-end-time="{{ $booking->end_time }}"

                                data-booking-status="{{ $booking->booking_status }}"
                                data-payment-status="{{ $booking->payment_status }}"
                                data-remarks="{{ $booking->remarks }}"
                                data-reason="{{ $booking->status_change_reason }}"
                            >
                                View
                            </button>




                            <!-- EDIT BUTTON -->
                            <button 
                                class="btn btn-sm btn-outline-primary editBookingBtn"
  

                                data-id="{{ $booking->id }}"

                                data-guest-name="{{ $booking->guest_name }}"
                                data-guest-email="{{ $booking->guest_email }}"
                                data-guest-contact="{{ $booking->guest_contact }}"

                                data-room-id="{{ $booking->room_id }}"
                                data-booking-date="{{ $booking->booking_date }}"

                                data-check-in="{{ $booking->check_in_date }}"
                                data-check-out="{{ $booking->check_out_date }}"
                                data-start-time="{{ $booking->start_time }}"
                                data-end-time="{{ $booking->end_time }}"

                                data-booking-status="{{ $booking->booking_status }}"
                                data-payment-status="{{ $booking->payment_status }}"
                                data-remarks="{{ $booking->remarks }}"
                                data-reason="{{ $booking->status_change_reason }}"
                            >
                                Edit
                            </button>



                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- nasuko ko -->


<!-- MODALS -->
<x-modal.calendar_filter />
<x-modal.edit_roombooking />
<x-modal.view_roombooking />



{{-- JS to populate edit modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {


    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('editBookingBtn')) {

            const button = e.target;
            const editForm = document.getElementById('editRoomBookingForm');

            const id = button.dataset.id;
            editForm.action = `/room-bookings/${id}`;

        
            document.getElementById('edit_guest_name').value = button.dataset.guestName;
            document.getElementById('edit_guest_email').value = button.dataset.guestEmail;
            document.getElementById('edit_guest_contact').value = button.dataset.guestContact;

            const checkIn = button.dataset.checkIn?.split(' ')[0] ?? '';
            document.getElementById('edit_check_in_date').value = checkIn;

            const checkOut = button.dataset.checkOut?.split(' ')[0] ?? '';
            document.getElementById('edit_check_out_date').value = checkOut;


            document.getElementById('edit_remarks').value = button.dataset.remarks ?? '';
            document.getElementById('edit_status_change_reason').value = button.dataset.reason ?? '';

            document.getElementById('edit_booking_status').value = button.dataset.bookingStatus;
            document.getElementById('edit_payment_status').value = button.dataset.paymentStatus;

            const modal = new bootstrap.Modal(document.getElementById('editRoomBookingModal'));
            modal.show();
        }


    });

    // reset modal on close
    document.getElementById('editRoomBookingModal')
        .addEventListener('hidden.bs.modal', () => editForm.reset());


    // populate view modal

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('viewBookingBtn')) {

            const button = e.target;

            document.getElementById('view_guest_name').innerText = button.dataset.guestName;
            document.getElementById('view_guest_email').innerText = button.dataset.guestEmail;
            document.getElementById('view_guest_contact').innerText = button.dataset.guestContact;

            document.getElementById('view_room').innerText = button.dataset.room;
            document.getElementById('view_booking_date').innerText = button.dataset.bookingDate;

            document.getElementById('view_check_in').innerText = button.dataset.checkIn;
            document.getElementById('view_check_out').innerText = button.dataset.checkOut;

            document.getElementById('view_start_time').innerText = button.dataset.startTime;
            document.getElementById('view_end_time').innerText = button.dataset.endTime;

            document.getElementById('view_booking_status').innerText = button.dataset.bookingStatus;
            document.getElementById('view_payment_status').innerText = button.dataset.paymentStatus;
            document.getElementById('view_reason').innerText = button.dataset.reason;

            const modal = new bootstrap.Modal(document.getElementById('viewRoomBookingModal'));
            modal.show();
        }
    });


        

});



</script>



<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS (Bootstrap 5) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>


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


@if(session('error'))
<script>
    var myModal = new bootstrap.Modal(document.getElementById('calendarFilterModal'));
    myModal.show();
</script>
@endif



@endsection
