<div class="sidebar-card">
    <nav class="d-flex flex-column gap-2">

        <a href="{{ route('admin.dashboard') }}" 
           class="nav-card-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('admin.rooms.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-closed"></i> Rooms
        </a>

        <a href="{{ route('admin.services.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
            <i class="bi bi-cone-striped"></i> Services
        </a>

        <a href="{{ route('admin.room_bookings.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.room_bookings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Room Bookings
        </a>

        <a href="{{ route('admin.service_bookings.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.service_bookings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i> Service Bookings
        </a>

        <a href="{{ route('admin.payments.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> Payments
        </a>

    </nav>
</div>
