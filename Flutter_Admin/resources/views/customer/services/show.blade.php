<x-customer.layout :title="$service->name">

{{-- DATA WRAPPER (non-visual, required for JS) --}}
<div data-service-id="{{ $service->id }}"
     data-service-type="{{ $service->service_type }}"
     data-start-time="{{ $service->start_time }}"
     data-end-time="{{ $service->end_time }}">

    {{-- ================= HEADER ================= --}}
    <section class="service-detail-header">
        <a href="{{ route('hotel.services') }}" class="back-link">← Back to Services</a>
        <h1 class="service-detail-title">{{ $service->name }}</h1>
    </section>

    {{-- ================= HERO IMAGE ================= --}}
    <section class="service-detail-hero">
        <img src="{{ $service->image
                ? asset('storage/' . $service->image)
                : asset('images/placeholder-service.jpg') }}"
             alt="{{ $service->name }}">
    </section>

    {{-- ================= CONTENT ================= --}}
    <section class="service-detail-content">

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="service-detail-main">

            <div class="service-detail-meta">
                <span>{{ ucfirst($service->service_type) }}</span>
                <span>•</span>
                <span>{{ $service->location }}</span>
            </div>

            <div class="service-detail-description">
                <p>{{ $service->description }}</p>
            </div>

            @if ($service->start_time && $service->end_time)
                <div class="service-detail-schedule">
                    <h3>Operating Hours</h3>
                    <p>{{ $service->start_time }} – {{ $service->end_time }}</p>
                </div>
            @endif

        </div>

        {{-- ===== BOOKING SIDEBAR (ENHANCED, DESIGN PRESERVED) ===== --}}
        <aside class="service-detail-sidebar">

            <form onsubmit="return false;">

                {{-- SERVICE DATE --}}
                <div class="availability-group">
                    <label>Service Date</label>
                    <input type="text"
                           id="service-date"
                           placeholder="Select date"
                           readonly>
                </div>

                {{-- TIME SLOT (ONLY FOR TIME-BASED SERVICES) --}}
                @if(in_array($service->service_type, ['spa','restaurant','bar']))
                    <div class="availability-group">
                        <label>Time Slot</label>
                        <select id="service-start-time" class="form-select">
                            <option value="">— Select Time Slot —</option>
                        </select>
                        <small class="text-muted">
                            1-hour slots
                        </small>
                    </div>
                @endif

                {{-- GUESTS --}}
                <div class="availability-group">
                    <label>Guests</label>
                    <input type="number"
                           id="service-guests"
                           min="1"
                           max="{{ $service->capacity }}"
                           value="1">
                </div>

                {{-- PRICE --}}
                <div class="price-box">
                    <div class="price-label">From</div>
                    <div class="price-value">
                        ₱{{ number_format($service->base_price) }}
                        <span>/ {{ str_replace('_', ' ', $service->price_type) }}</span>
                    </div>
                </div>

                {{-- CTA --}}
                <button type="button"
                        id="book-service-btn"
                        class="book-service-btn"
                        disabled>
                    Select Date
                </button>

                <p id="service-availability-message" class="booking-note">
                    Select a date and guest count to check availability.
                </p>

            </form>

        </aside>

    </section>

</div>

{{-- ================= STYLES ================= --}}
@push('styles')
<style>

/* ================= SERVICE DETAIL PAGE ================= */

.service-detail-header {
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

.service-detail-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 3.4rem;
}

/* ================= HERO IMAGE ================= */

.service-detail-hero img {
    width: 100%;
    height: 480px;
    object-fit: cover;
}

/* ================= CONTENT ================= */

.service-detail-content {
    padding: 4rem 9rem 6rem;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 5rem;
}

/* Main content */
.service-detail-meta {
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    margin-bottom: 2rem;
    color: rgba(0,0,0,0.7);
}

.service-detail-description {
    font-size: 1.05rem;
    line-height: 1.8;
    max-width: 620px;
}

.service-detail-schedule {
    margin-top: 3rem;
}

.service-detail-schedule h3 {
    font-size: 1rem;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.service-detail-schedule p {
    font-size: 0.95rem;
    color: rgba(0,0,0,0.7);
}

/* Sidebar */
.service-detail-sidebar {
    border-left: 1px solid rgba(0,0,0,0.1);
    padding-left: 3rem;
}

.price-box {
    margin-bottom: 2.5rem;
}

.price-label {
    font-size: 0.8rem;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
}

.price-value {
    font-size: 2.2rem;
    font-weight: 600;
}

.price-value span {
    font-size: 1rem;
    font-weight: normal;
}

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

.booking-note {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: rgba(0,0,0,0.6);
}

</style>
@endpush

{{-- ================= SCRIPTS ================= --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', async function () {

    const input = document.getElementById('service-date');
    if (!input) return;

    const serviceId = "{{ $service->id }}";

    let disabledDates = [];

    try {
        const res = await fetch(`/hotel/services/${serviceId}/booked-dates`);
        if (res.ok) disabledDates = await res.json();
    } catch (e) {
        console.warn('Failed to load disabled dates');
    }

    flatpickr(input, {
        appendTo: document.body,
        dateFormat: "Y-m-d",
        minDate: "today",
        disable: disabledDates,

        onChange: function (selectedDates, dateStr) {
            if (typeof window.onDateChange === 'function') {
                window.onDateChange(dateStr);
            }
        }
    });
});
</script>

{{-- Service booking logic (slots + availability) --}}
<script src="{{ asset('customer/js/pages/service-detail.js') }}"></script>

@endpush

</x-customer.layout>
