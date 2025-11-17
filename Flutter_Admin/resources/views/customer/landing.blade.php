@extends('customer.layouts.hotel')

@section('content')

<!-- BOOKING RIBBON -->
<div class="booking-ribbon shadow-lg p-4 rounded-4 mb-5"
     style="background: #7C4A3A; color: #F3E6D6;
            margin-top: -90px; 
            position: relative; 
            z-index: 20; 
            width: 92%; 
            margin-left: auto; 
            margin-right: auto;
            border-radius: 20px;">

    <!-- TABS -->
    <ul class="nav nav-tabs border-0 mb-4">
        <li class="nav-item">
            <a class="nav-link active px-4 py-2 fw-bold"
               style="background:#A55B44; color:#F3E6D6; border-radius:6px 6px 0 0;">
                <i class="bi bi-door-open me-1"></i> Rooms
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link px-4 py-2 fw-bold" style="color:#F3E6D6;">
                <i class="bi bi-sun"></i> Services
            </a>
        </li>
    </ul>

    <!-- CONTENT -->
    <div class="row align-items-center">

        <!-- DATE PICKER ONLY -->
        <div class="col-md-8 mb-3">
            <label class="fw-bold mb-1" style="color:#F3E6D6;">Date *</label>
            <div class="p-3 rounded-3 d-flex align-items-center"
                style="background:#A55B44; cursor:pointer;">
                <i class="bi bi-calendar3 fs-5 me-3" style="color:#E9A46F;"></i>
                
                <input type="text"
                    id="booking_date"
                    class="form-control border-0 bg-transparent text-white"
                    placeholder="Select Date"
                    style="box-shadow:none; outline:none;">
            </div>
        </div>

        <!-- BOOK BUTTON -->
        <div class="col-md-4 text-md-end mt-4 mt-md-0">
            <a href="{{ route('hotel.book.room') }}"
            class="btn px-5 py-3 fw-bold"
            style="background:#E9A46F; color:#7C4A3A; border-radius:10px;">
                BOOK NOW
            </a>
        </div>

    </div>

</div>


<!-- ABOUT SECTION -->
<div class="container my-5">
    <div class="row align-items-center mb-5">

        <!-- IMAGE -->
        <div class="col-md-5 mb-4 mb-md-0">
            <img src="{{ asset('images/hotel_lobby.jpg') }}" 
                alt="Hotel Crepúsculo"
                class="img-fluid rounded-4 shadow">
        </div>

        <!-- ABOUT TEXT -->
        <div class="col-md-7">
            <h2 class="fw-bold text-uppercase mb-3" style="color: #A55B44;">About Us</h2>

            <p class="lead" style="line-height: 1.8; color:#7C4A3A;">
                At <strong>Hotel Crepúsculo</strong>, we bring warmth, comfort, and elegance together 
                in a peaceful destination crafted to rejuvenate your senses.
                Our interiors reflect the hues of twilight, embracing you in a soft and tranquil ambience.
            </p>

            <p style="line-height: 1.8; color:#7C4A3A;">
                From thoughtfully curated suites to premium hospitality services, 
                our philosophy is simple: create experiences that linger in memory.
                Whether you're here for leisure, business, or celebration, 
                our environment is designed to make every moment feel special.
            </p>
        </div>

    </div>
</div>


<!-- CONTACT SECTION -->
<div class="container my-5 pt-4">
    <h2 class="fw-bold text-uppercase mb-3" style="color: #A55B44;">Contact Us</h2>

    <div class="p-4 rounded-4 shadow"
         style="background: #D8C3B4; border-left: 6px solid #A55B44;">

        <p class="mb-2" style="color:#7C4A3A;"><strong>Email:</strong> info@hotelcrepusculo.com</p>
        <p class="mb-2" style="color:#7C4A3A;"><strong>Phone:</strong> +123 456 789</p>
        <p class="mb-2" style="color:#7C4A3A;"><strong>Location:</strong> Twilight Avenue, Sunset City</p>

    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    $('#booking_date').datepicker({
        format: 'mm/dd/yyyy',
        autoclose: true,
        todayHighlight: true
    });
});
</script>
@endpush

