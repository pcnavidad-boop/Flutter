<!-- Topbar component (floating card) -->
<div class="topbar-card">

    {{-- Left: Logo + Brand Name --}}
    <div class="topbar-brand d-flex align-items-center">
        <x-application-logo class="topbar-logo" />

        <span class="brand-title" style="
            font-size: 28px;
            font-weight: 700;
            color: var(--accent-brown-deep);
            letter-spacing: .3px;
            white-space: nowrap;
        ">
            Hotel Crespusculo
        </span>
    </div>

    {{-- Right Side Controls --}}
    <div class="topbar-controls">

        {{-- Notification Bell --}}
        <div class="dropdown">
            <button class="btn p-0 border-0 bg-transparent notif-clean"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="font-size: 22px; color: var(--accent-brown-deep);">
                <i class="bi bi-bell-fill"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 320px;">
                <li class="dropdown-header small text-muted">Notifications</li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item text-center text-muted py-3">No notifications yet</li>
            </ul>
        </div>

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button class="btn dropdown-toggle fw-semibold"
                data-bs-toggle="dropdown"
                style="background:none; border:none; color:#333;">
                {{ Auth::user()->name }}
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">Account</h6></li>

                <li><a class="dropdown-item" href="#">
                    <i class="bi bi-person me-2"></i> Profile
                </a></li>

                <li><a class="dropdown-item" href="#">
                    <i class="bi bi-gear me-2"></i> Settings
                </a></li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</div>

<style>
.notif-clean {
    transition: transform .15s ease, color .15s ease;
}
.notif-clean:hover {
    transform: translateY(-2px);
    color: var(--accent-brown);
}
</style>
