@extends('customer.layouts.hotel')

@section('content')

<h1 class="mb-4">Book a Service</h1>

<form>

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" placeholder="Enter your full name">
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control" placeholder="Enter your email">
    </div>

    <div class="mb-3">
        <label class="form-label">Select Date</label>
        <input type="date" class="form-control">
    </div>

    <button class="btn btn-hotel mt-3">Submit Booking</button>

</form>

@endsection
