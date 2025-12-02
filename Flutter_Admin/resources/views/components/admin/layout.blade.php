<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Admin Panel' }}</title>

    <!-- Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --accent-brown: #c7a47a;
            --accent-brown-dark: #9c7b55;
            --accent-brown-deep: #6b4a2b;
            --page-bg: #f7f7f9;
            --card-bg: #ffffff;
            --muted-text: #5b5b5b;
            --card-shadow: 0 8px 28px rgba(20,20,30,0.04);
            --card-border: 1px solid rgba(18,18,18,0.04);
        }

        /* ===== GLOBAL ===== */
        html, body { height:100%; }
        body {
            margin: 0;
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: var(--page-bg);
            color: #222;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== SHELL PADDING (matches topbar left/right spacing) ===== */
        .app-shell {
            min-height: 100vh;
            position: relative;
            padding: 20px 24px; /* << reduced to align content with topbar edges */
            box-sizing: border-box;
        }

        /* ===============================
           BUTTON THEMING (coffee)
        ================================ */
        .btn-coffee {
            background: var(--accent-brown);
            color: #fff;
            border-radius: 25px;
            padding: 8px 18px;
            font-weight:600;
            border: none;
            transition: transform .15s ease, background .15s ease;
            box-shadow: 0 4px 10px rgba(139,94,60,0.06);
        }
        .btn-coffee:hover { background: var(--accent-brown-dark); transform: translateY(-2px); color:#fff; }

        .btn-outline-coffee {
            background: rgba(199,164,122,0.10);
            border: 2px solid var(--accent-brown);
            color: var(--accent-brown-deep);
            border-radius: 25px;
            padding: 8px 16px;
            font-weight:600;
            transition: all .15s ease;
        }
        .btn-outline-coffee:hover { background: var(--accent-brown); color:#fff; transform: translateY(-2px); }

        /* ===============================
           TOPBAR (floating card)
        ================================ */
        .topbar-card {
            position: fixed;
            top: 20px;
            left: 24px;
            width: calc(100% - 48px); /* matches app-shell horizontal padding */
            height: 72px;

            background: var(--card-bg);
            border-radius: 14px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding: 0 20px;
            box-shadow: var(--card-shadow);
            border: var(--card-border);
            z-index: 1050;
        }

        .topbar-brand { display:flex; align-items:center; gap:12px; }
        .topbar-controls { display:flex; align-items:center; gap:16px; }

        .topbar-logo { height:44px; width:auto; object-fit:contain; }

        /* ===============================
           SIDEBAR (floating card)
        ================================ */
        .sidebar-card {
            position: fixed;
            top: 112px; /* sits neatly under topbar */
            left: 24px;
            width: 260px;
            max-height: calc(100vh - 140px);
            background: var(--card-bg);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: var(--card-shadow);
            border: var(--card-border);
            overflow:auto;
            z-index: 1030;
        }

        .nav-card-list { display:flex; flex-direction:column; gap:8px; margin-top:8px; }

        .nav-card-item {
            display:flex; align-items:center; gap:12px;
            padding:10px 12px; border-radius:10px;
            color: var(--muted-text); text-decoration:none; font-weight:600;
            transition: all .16s ease;
        }
        .nav-card-item i { font-size:18px; color: var(--accent-brown-dark); }
        .nav-card-item:hover { background: rgba(199,164,122,0.06); color:#222; transform: translateY(-2px); }
        .nav-card-item.active {
            background: linear-gradient(90deg, rgba(199,164,122,0.12), rgba(199,164,122,0.06));
            border-left: 4px solid var(--accent-brown);
            color: var(--accent-brown-dark);
            box-shadow: 0 6px 18px rgba(199,164,122,0.06);
        }

        /* ===============================
           CONTENT / PAGE CARDS
        ================================ */
        .content-wrap {
            margin-left: 300px; /* leaves space for 260px sidebar + gap */
            margin-top: 115px;  /* sits below topbar */
            padding-left: 0;
            padding-right: 0;
            padding-bottom: 40px;
            box-sizing: border-box;
        }

        .page-card, .content-card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            border: var(--card-border);
            margin-bottom: 20px;
            margin-right: 0; /* ensures right edge aligns with topbar */
        }

        .page-card h2, .content-card h2 { margin:0 0 12px 0; font-weight:700; color:var(--accent-brown-deep); }

        /* Filters inputs (ensures they don't stretch) */
        .filter-row .filter-input {
            width: 160px !important;
            min-width: 140px;
        }
        .filter-row input.filter-input {
            width: 220px !important;
        }

        /* small screens */
        @media (max-width: 1200px) {
            .sidebar-card { left: 20px; width: 220px; }
            .content-wrap { margin-left: 250px; margin-top: 130px; padding: 0 18px 40px; }
        }

        @media (max-width: 768px) {
            .sidebar-card { display:none; }
            .topbar-card { width: calc(100% - 40px); left: 20px; }
            .content-wrap { margin-left: 0; margin-top: 120px; padding: 0 12px 40px; }
            .filter-row .filter-input { width: 100% !important; min-width: 0; }
        }

    </style>

    {{ $head ?? '' }}
</head>
<body>
    <div class="app-shell">

        {{-- TOPBAR --}}
        <x-admin.navbar />

        {{-- SIDEBAR --}}
        <x-admin.sidebar />

        {{-- MAIN CONTENT WRAPPER --}}
        <main class="content-wrap">
            {{ $slot }}
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- TOAST CONTAINER (preserved logic) --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        @if(session('success'))
            <div class="toast text-bg-success border-0 show">
                <div class="d-flex">
                    <div class="toast-body">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast text-bg-danger border-0 show">
                <div class="d-flex">
                    <div class="toast-body">{{ session('error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
    </div>

    {{-- Page-specific scripts --}}
    @stack('scripts')

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".toast").forEach((toastEl) => {
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 1800 
            });
            toast.show();
        });
    });
    </script>

</body>
</html>
