<!-- Sidebar floating card -->
<div class="sidebar-card">

    {{-- Spacer so content does not stick to the edge --}}
    <div style="height: 4px;"></div>

    {{-- Navigation --}}
    <nav class="nav-card-list">

        <a href="{{ route('admin.dashboard') }}"
           class="nav-card-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.rooms.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-closed"></i>
            <span>Rooms</span>
        </a>

        <a href="{{ route('admin.services.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
            <i class="bi bi-cone-striped"></i>
            <span>Services</span>
        </a>

        <a href="{{ route('admin.room_bookings.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.room_bookings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i>
            <span>Room Bookings</span>
        </a>

        <a href="{{ route('admin.service_bookings.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.service_bookings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i>
            <span>Service Bookings</span>
        </a>

        <a href="{{ route('admin.payments.index') }}"
           class="nav-card-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i>
            <span>Payments</span>
        </a>

    </nav>
</div>
