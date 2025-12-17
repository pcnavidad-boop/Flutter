<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Hotel Crepúsculo' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            --ivory: #F4EFE9;
            --paper: #FFFFFF;
            --espresso: #2F221C;
            --mocha: #5A3E31;
            --brass: #C5A25D;
        }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--espresso);
            font-family: 'Inter', sans-serif;
        }

        .content-wrapper {
            padding-top: 150px;
        }

        /* ================= NAVBAR ================= */
        .hotel-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 120px;
            background: var(--ivory);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            z-index: 1000;
        }

        .nav-left,
        .nav-right {
            display: flex;
            gap: 3rem;
        }

        .nav-link {
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--espresso);
            text-decoration: none;
            position: relative;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            bottom: -6px;
            left: 50%;
            width: 0;
            height: 1px;
            background: var(--brass);
            transition: all .3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        .nav-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
        }

        .brand-logo {
            height: 50px;
            margin-bottom: 6px;
        }

        .brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--espresso);
        }

        /* ================= HERO ================= */
        .hero-section {
            position: relative;
            height: 92vh;
        }

        .hero-bg {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 7.5rem;
            color: #fff;
            text-align: center;
            line-height: 1.1;
            letter-spacing: 1.2px;
        }

        /* ================= STAY PICKER ================= */
        .stay-picker {
            background: var(--mocha);
            padding: 3.5rem 0;
            text-align: center;
        }

        .stay-title {
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 3px;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 2.5rem;
        }

        .stay-form {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .stay-form input {
            padding: 1rem 1.4rem;
            border: none;
            width: 250px;
            font-family: 'Inter', sans-serif;
        }

        .stay-btn {
            background: var(--brass);
            border: none;
            padding: 1rem 2.8rem;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        /* ===== Stay Picker Fields ===== */
        .stay-field {
            display: flex;
            align-items: stretch;
        }

        .stay-label {
            background: #4A3126; /* darker than --mocha */
            color: #fff;
            padding: 1rem 1.4rem;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .stay-field input {
            padding: 1rem 1.4rem;
            border: none;
            width: 260px;
            font-family: 'Inter', sans-serif;
        }

        /* Guests input narrower */
        .stay-field input[type="number"] {
            width: 160px;
        }

        /* Remove default number arrows styling quirks (optional polish) */
        .stay-field input[type="number"]::-webkit-inner-spin-button,
        .stay-field input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }

        /* ===== Stay Picker Room Select (Enhanced) ===== */
        .stay-field select {
            padding: 1rem 1.4rem;
            border: none;
            width: 260px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            color: var(--espresso);
            background-color: #fff;
            appearance: none;
            cursor: pointer;

            background-image:
                linear-gradient(45deg, transparent 50%, var(--espresso) 50%),
                linear-gradient(135deg, var(--espresso) 50%, transparent 50%);
            background-position:
                calc(100% - 18px) 50%,
                calc(100% - 13px) 50%;
            background-size: 5px 5px;
            background-repeat: no-repeat;
        }

        /* Disabled state */
        .stay-field select:disabled {
            background-color: #f3f0ec;
            color: rgba(0,0,0,0.45);
            cursor: not-allowed;
        }

        /* ================= FLATPICKR – HOTEL CREPÚSCULO THEME ================= */

        .flatpickr-calendar {
            font-family: 'Inter', sans-serif;
            background: var(--paper);
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-radius: 6px;
        }

        /* ===== Month + Year ===== */
        .flatpickr-months {
            background: var(--paper);
        }

        .flatpickr-current-month {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .flatpickr-month {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            letter-spacing: 1px;
            color: var(--espresso);
        }

        .flatpickr-current-month input.cur-year {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            font-weight: 600;
            width: 4.2ch;
            color: var(--espresso);
            background: transparent;
            border: none;
        }

        /* ===== Navigation Arrows ===== */
        .flatpickr-prev-month,
        .flatpickr-next-month {
            color: var(--espresso);
        }

        .flatpickr-prev-month:hover,
        .flatpickr-next-month:hover {
            color: var(--brass);
        }

        /* ===== Weekdays ===== */
        .flatpickr-weekdays {
            background: var(--paper);
        }

        .flatpickr-weekday {
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--mocha);
        }

        /* ===== Days ===== */
        .flatpickr-day {
            border-radius: 50%;
            color: var(--espresso);
        }

        /* Normal hover (non-selected days only) */
        .flatpickr-day:not(.selected):not(.startRange):not(.endRange):hover {
            background: var(--ivory);
        }

        /* ===== FORCE OVERRIDE — ALL SELECTED STATES ===== */
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover,
        .flatpickr-day.selected:focus,
        .flatpickr-day.selected.inRange,
        .flatpickr-day.selected.startRange,
        .flatpickr-day.selected.endRange,
        .flatpickr-day.selected.focused {
            background: var(--brass) !important;
            color: #fff !important;
            border: none !important;
        }

        /* ===== Range edges (start & end) ===== */
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: var(--brass) !important;
            color: #fff !important;
            border: none !important;
        }

        /* Kill BLUE hover/focus on range edges */
        .flatpickr-day.startRange:hover,
        .flatpickr-day.endRange:hover,
        .flatpickr-day.startRange:focus,
        .flatpickr-day.endRange:focus,
        .flatpickr-day.startRange.focused,
        .flatpickr-day.endRange.focused {
            background: var(--brass) !important;
            color: #fff !important;
            border: none !important;
        }

        /* ===== In-between range ===== */
        .flatpickr-day.inRange {
            background: rgba(197, 162, 93, 0.25);
            color: var(--espresso);
        }

        /* ===== Today ===== */
        .flatpickr-day.today {
            border: 1px solid var(--brass);
        }

        /* ===== Disabled / outside month ===== */
        .flatpickr-day.disabled,
        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: rgba(0,0,0,0.25);
        }

        .flatpickr-calendar {
            z-index: 2000 !important;
        }

        /* ================= ABOUT ================= */
        .about-section {
            background: var(--paper);
            padding: 7rem 9rem;
        }

        .about-subtitle {
            letter-spacing: 2.5px;
            font-size: .9rem;
            margin-bottom: .5rem;
        }

        .about-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3.2rem;
            margin-bottom: 3.5rem;
        }

        .about-content {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 5rem;
        }

        .about-content p {
            font-size: 1.15rem;
            line-height: 1.8;
            margin-bottom: 1.8rem;
        }

        /* ================= DIVIDER ================= */
        .section-divider {
            height: 1px;
            background: var(--mocha);
            margin: 6rem 9rem;
        }

        /* ================= GALLERY ================= */
        .gallery-section {
            background: var(--paper);
            padding: 5rem 9rem;
        }

        .gallery-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.8rem;
            margin-bottom: 4rem;
            text-align: center;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
        }

        .gallery-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ================= CONTACT ================= */
        .contact-section {
            background: var(--mocha);
            color: #fff;
            padding: 3rem 9rem;
        }

        .contact-inner {
            max-width: 420px;
            margin: 0 auto;
            text-align: center;
        }

        .contact-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.2rem;
            margin-bottom: 1.2rem;
        }

        /* Offset anchor scroll for fixed navbar */
        section[id] {
            scroll-margin-top: 150px; /* navbar height + breathing room */
        }

        /* ================= CUSTOMER FILTERS (Shared) ================= */

        .rooms-filters,
        .services-filters {
            padding: 0 9rem 2.5rem;
            display: flex;
            gap: 3rem;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .filter-label {
            font-size: 0.7rem;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: rgba(0,0,0,0.55);
        }

        .filter-select {
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(0,0,0,0.25);
            padding: 0.4rem 0;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: var(--espresso);
            min-width: 140px;
        }

        .filter-select:focus {
            outline: none;
            border-bottom-color: var(--brass);
        }

        /* ================= BOOKING SIDEBAR (Shared: Rooms + Services) ================= */

        .availability-group {
            margin-bottom: 1.2rem;
        }

        .availability-group label {
            display: block;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
            color: rgba(0,0,0,0.6);
        }

        .availability-group input,
        .availability-group select {
            width: 100%;
            padding: 0.6rem 0;
            border: none;
            border-bottom: 1px solid rgba(0,0,0,0.3);
            background: transparent;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }

        .availability-group input:focus,
        .availability-group select:focus {
            outline: none;
            border-bottom-color: var(--brass);
        }

        .availability-group select {
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, var(--espresso) 50%),
                linear-gradient(135deg, var(--espresso) 50%, transparent 50%);
            background-position:
                calc(100% - 12px) 50%,
                calc(100% - 7px) 50%;
            background-size: 5px 5px;
            background-repeat: no-repeat;
        }

        .booking-note {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: rgba(0,0,0,0.6);
        }

        .book-room-btn,
        .book-service-btn {
            width: 100%;
            padding: 1rem;
            font-size: 0.95rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            background: var(--brass);
            color: #fff;
            border: none;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .book-room-btn:not(:disabled),
        .book-service-btn:not(:disabled) {
            cursor: pointer;
            opacity: 1;
        }

        /* ================= SHARED PAGE HEADER ================= */

        .room-detail-header {
            padding: 5rem 9rem 2rem;
        }

        .back-link {
            font-size: 0.85rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--espresso);
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .room-detail-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3.4rem;
        }

        /* ================= FORM VALIDATION (Admin Parity) ================= */

        /* Base input */
        .booking-form input,
        .booking-form textarea {
            border: 1px solid #cfcfcf;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        /* VALID */
        .booking-form .is-valid {
            border-color: #198754;
            background-color: #f8fffb;
            box-shadow: 0 0 0 0.15rem rgba(25, 135, 84, 0.15);
        }

        /* INVALID */
        .booking-form .is-invalid {
            border-color: #dc3545;
            background-color: #fff8f8;
            box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.15);
        }

        /* Validation hint text */
        .booking-form .validation-hint {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .booking-form .validation-hint.valid {
            color: #198754;
        }

        .booking-form .validation-hint.invalid {
            color: #dc3545;
        }

        /* ================= BOOKING LAYOUT (Rooms + Services) ================= */

        .booking-layout {
            padding: 3rem 9rem 6rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 5rem;
        }

        .booking-form h2 {
            margin-bottom: 2rem;
            font-size: 1.4rem;
        }

        .booking-form textarea {
            width: 100%;
            resize: vertical;
        }

        /* ================= BOOKING SUMMARY ================= */

        .booking-summary {
            border-left: 1px solid rgba(0,0,0,0.1);
            padding-left: 3rem;
        }

        .booking-summary h3 {
            margin-bottom: 2rem;
            font-size: 1.2rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

    </style>

    @stack('styles')
</head>
<body>

<x-customer.navbar />

<main class="content-wrapper">
    {{ $slot }}
</main>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@stack('scripts')

</body>
</html>
