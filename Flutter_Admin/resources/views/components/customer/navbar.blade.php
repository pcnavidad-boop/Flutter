<nav class="hotel-navbar">

    <!-- LEFT -->
    <div class="nav-left">
        <a href="{{ route('hotel.rooms') }}" class="nav-link">Rooms</a>
        <a href="{{ route('hotel.services') }}" class="nav-link">Services</a>
    </div>

    <!-- CENTER -->
    <div class="nav-center">
        <a href="{{ route('hotel.landing') }}" class="brand">
            <img src="{{ asset('images/logo.png') }}"
                 class="brand-logo"
                 alt="Hotel Crepúsculo">
            <span class="brand-name">Hotel Crepúsculo</span>
        </a>
    </div>

    <!-- RIGHT -->
    <div class="nav-right">
        <a href="#about" class="nav-link">About</a>
        <a href="#gallery" class="nav-link">Gallery</a>
        <a href="#contact" class="nav-link">Contact</a>
    </div>

</nav>
