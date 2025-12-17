<div class="topbar-card">

    <div class="d-flex align-items-center gap-2">
        <x-application-logo class="topbar-logo" style="height:42px;" />
        <span class="fw-bold" style="font-size:26px; color:var(--accent-brown-deep);">
            Hotel Crespusculo
        </span>
    </div>

    <div class="d-flex align-items-center gap-4">

        {{-- Notification Bell --}}
        <div class="dropdown">
            <button class="btn notif-clean p-0 border-0 bg-transparent" data-bs-toggle="dropdown">
                <i class="bi bi-bell-fill" style="font-size: 22px; color: var(--accent-brown-deep);"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="width:320px;">
                <li class="dropdown-header small text-muted">Notifications</li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item text-center text-muted py-3">No notifications yet</li>
            </ul>
        </div>

        {{-- User --}}
        <div class="dropdown">
            <button class="btn dropdown-toggle fw-semibold" data-bs-toggle="dropdown">
                {{ Auth::user()->name }}
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">Account</h6></li>
                <li><a class="dropdown-item"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><a class="dropdown-item"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">@csrf
                        <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</div>
