<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid">

        {{-- Keep sidebar component (it can render its own toggle button for mobile) --}}
        <div class="me-3 d-flex align-items-center">
            <x-sidebar />
        </div>

        {{-- Brand --}}
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">Flutter</a>

        {{-- Right side: username dropdown placed outside of .collapse so it's always visible --}}
        <div class="d-flex align-items-center ms-auto">
            @auth
                {{-- Visible username (no collapse involved) --}}
                <div class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle text-white d-flex align-items-center"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <span id="topbar-username">{{ Auth::user()->name }}</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-gear me-2"></i> Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>
</nav>
