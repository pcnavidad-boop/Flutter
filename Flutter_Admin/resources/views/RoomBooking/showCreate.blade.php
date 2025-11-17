@extends('layouts.rooms')

@section('content')

<!-- DataTables CSS (for consistent theme spacing/fonts even if not used here) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<x-alert_message></x-alert_message>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Add New Booking</h3>
        <a href="{{ route('roombookings.index_page') }}" class="btn btn-secondary">
            ← Back to Bookings
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-4">Booking Details</h5>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some problems with your input:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('roombookings.create') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="guest_name" class="form-label fw-semibold">Guest Name</label>
                        <input type="text" name="guest_name" id="guest_name" class="form-control" value="{{ old('guest_name') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="guest_email" class="form-label fw-semibold">Guest Email</label>
                        <input type="email" name="guest_email" id="guest_email" class="form-control" value="{{ old('guest_email') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="guest_contact" class="form-label fw-semibold">Guest Contact</label>
                        <input type="text" name="guest_contact" id="guest_contact" class="form-control" value="{{ old('guest_contact') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="room_id" class="form-label fw-semibold">Select Room</label>
                        <select name="room_id" id="room_id" class="form-select" required>
                            <option value="">-- Select Room --</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->room_number ?? $room->room_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="booking_date" class="form-label fw-semibold">Booking Date</label>
                        <input type="date" name="booking_date" id="booking_date" class="form-control" value="{{ old('booking_date') }}" required>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="check_in_date" class="form-label fw-semibold">Check-In Date</label>
                        <input type="date" name="check_in_date" id="check_in_date" class="form-control" value="{{ old('check_in_date') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="check_out_date" class="form-label fw-semibold">Check-Out Date</label>
                        <input type="date" name="check_out_date" id="check_out_date" class="form-control" value="{{ old('check_out_date') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="event_date" class="form-label fw-semibold">Event Date</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" value="{{ old('event_date') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label fw-semibold">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_time" class="form-label fw-semibold">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status_change_reason" class="form-label fw-semibold">Status Change Reason (optional)</label>
                    <textarea name="status_change_reason" id="status_change_reason" class="form-control" rows="3">{{ old('status_change_reason') }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">Save Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
