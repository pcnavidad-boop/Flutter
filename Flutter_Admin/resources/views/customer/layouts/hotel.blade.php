<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Crepúsculo</title>

    <!-- jQuery (required for Bootstrap Datepicker) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


    
    <style>
        :root {
            --twilight-orange: #E9A46F;
            --sunset-brown: #A55B44;
            --sand-beige: #F3E6D6;
            --clay-neutral: #D8C3B4;
            --light-cocoa: #7C4A3A;
        }

        body {
            background: var(--sand-beige);
            color: var(--light-cocoa);
            font-family: "Georgia", serif;
        }

        .text-body,
        p,
        .form-control,
        label,
        input,
        textarea,
        .card p,
        .card label {
            font-family: 'Inter', sans-serif !important;
        }

        /* NAVBAR */
        .navbar {
            background: var(--sunset-brown);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.4rem;
            color: var(--sand-beige) !important;
        }

        .nav-link {
            color: var(--sand-beige) !important;
            font-weight: 500;
            padding: 12px 18px !important;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
        }

        /* HERO SECTION */
        .hero-section {
            height: 520px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            border-bottom: 6px solid var(--sunset-brown);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
        }

        .hero-text {
            position: absolute;
            bottom: 60px;
            left: 50px;
            color: white;
            font-size: 3.2rem;
            font-weight: bold;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.4);
        }

        /* BOOKING RIBBON */
        .booking-ribbon {
            background: var(--light-cocoa);
            color: var(--sand-beige);
        }

        /* BUTTONS */
        .btn-hotel {
            background: var(--twilight-orange);
            color: #3a2a2a;
            font-weight: bold;
            border-radius: 30px;
            padding: 10px 22px;
            border: none;
        }

        .btn-hotel:hover {
            background: #e3a06c;
        }

        /* CARDS */
        .card {
            background: var(--clay-neutral);
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        footer {
            background: var(--sunset-brown);
            color: var(--sand-beige);
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg py-3 shadow-sm">
    <div class="container">

        <a href="{{ route('hotel.landing') }}" class="navbar-brand d-flex align-items-center">

            <img src="{{ asset('logo_sym.png') }}"
                 style="height: 38px; width: auto; margin-right: 10px;"
                 alt="Hotel Logo">

            <span>HOTEL CREPÚSCULO</span>
        </a>

        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#hotelNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="hotelNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="{{ route('hotel.landing') }}" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="{{ route('hotel.rooms') }}" class="nav-link">Rooms</a></li>
                <li class="nav-item"><a href="{{ route('hotel.services') }}" class="nav-link">Services</a></li>
            </ul>
        </div>

    </div>
</nav>

<!-- HERO IMAGE -->
<div class="hero-section" style="background-image: url('/images/hotel_cover.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-text">Luxury Awaits</div>
</div>

<!-- MAIN CONTENT -->
<div class="container py-5">
    @yield('content')
</div>

<!-- FOOTER -->
<footer class="text-center py-4 mt-5">
    © {{ date('Y') }} Hotel Crepúsculo — All Rights Reserved
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
@stack('scripts')
</html>
