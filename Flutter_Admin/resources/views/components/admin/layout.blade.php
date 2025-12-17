<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Admin Panel' }}</title>

    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --accent-brown: #c7a47a;
            --accent-brown-dark: #9c7b55;
            --accent-brown-deep: #6b4a2b;
            --page-bg: #f7f7f9;
            --card-bg: #ffffff;
            --card-shadow: 0 8px 28px rgba(20,20,30,0.04);

            --gutter: 32px;
            --sidebar-width: 260px;
            --topbar-height: 72px;
        }

        body {
            font-family: "Inter", system-ui;
            background: var(--page-bg);
            margin: 0;
        }

        /* ============ TOPBAR ============ */
        .topbar-card {
            position: fixed;
            top: 20px;
            left: var(--gutter);
            right: var(--gutter);
            height: var(--topbar-height);
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: var(--card-shadow);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
        }

        /* ============ SIDEBAR ============ */
        .sidebar-card {
            position: fixed;
            top: calc(20px + var(--topbar-height) + 20px);
            left: var(--gutter);
            width: var(--sidebar-width);
            height: calc(100vh - (var(--topbar-height) + 60px));
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            overflow-y: auto;
        }

        .nav-card-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-weight: 600;
            color: #555;
            text-decoration: none !important;
        }

        .nav-card-item:hover {
            background: rgba(199,164,122,0.08);
            color: #222;
        }

        .nav-card-item.active {
            background: rgba(199,164,122,0.15);
            color: var(--accent-brown-dark);
            border-left: 4px solid var(--accent-brown);
        }

        /* ============ CONTENT ============ */
        .content-wrap {
            padding-top: calc(var(--topbar-height) + 70px);
            padding-left: calc(var(--sidebar-width) + var(--gutter) + 24px);
            padding-right: var(--gutter);
            padding-bottom: 40px;
        }

        .content-card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        /* ============ FILTER BAR ============ */
        .filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-grow: 1;
        }

        .filter-controls .form-control,
        .filter-controls .form-select {
            min-width: 200px;
            flex: 1;
        }

        .filter-bar > .btn-coffee {
            margin-left: 250px;
        }

        @media (max-width: 1100px) {
            .filter-bar,
            .filter-controls {
                flex-wrap: wrap;
            }
        }

        /* BUTTONS */
        .btn-coffee {
            background: var(--accent-brown);
            color: #fff;
            border-radius: 25px;
            padding: 8px 18px;
            border: none;
        }

        .btn-outline-coffee {
            background: rgba(199,164,122,0.10);
            border: 2px solid var(--accent-brown);
            color: var(--accent-brown-deep);
            border-radius: 25px;
            padding: 8px 18px;
        }
    </style>

    {{ $head ?? '' }}
</head>

<body>

    <x-admin.navbar />
    <x-admin.sidebar />

    <main class="content-wrap">
        {{ $slot }}
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {

        // ADD MODAL CALENDAR
        if (document.getElementById("addRoomBookingModal")) {
            window.addCal = AdminRoomBookingCalendar.CalendarInstance({
                containerId: "add-cal-container",
                labelId: "add-cal-label",
                prevId: "add-cal-prev",
                nextId: "add-cal-next",
                roomId: "add-room-id",
                startInput: "add-start-date",
                endInput: "add-end-date",
                modalId: "addRoomBookingModal"
            });
        }

        // EDIT MODAL CALENDAR
        if (document.getElementById("editRoomBookingModal")) {
            window.editCal = AdminRoomBookingCalendar.CalendarInstance({
                containerId: "edit-cal-container",
                labelId: "edit-cal-label",
                prevId: "edit-cal-prev",
                nextId: "edit-cal-next",
                roomId: "edit-room-id",
                startInput: "edit-start-date",
                endInput: "edit-end-date",
                modalId: "editRoomBookingModal"
            });
        }

    });
    </script>

    {{-- GLOBAL BOOTSTRAP TOASTS --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 2000;"> 

        @if (session('success'))
            <div class="toast align-items-center text-bg-success border-0"
                role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast align-items-center text-bg-danger border-0"
                role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- LOAD CORE SCRIPTS -->
    <script src="/admin/js/core/bootstrap.js"></script>

    <!-- LOAD MAIN ADMIN SCRIPT -->
    <script src="/admin/js/admin.js"></script>
    
    @stack('scripts')

</body>
</html>
