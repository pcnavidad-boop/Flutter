<x-customer.layout title="Booking Confirmed">

{{-- ================= HEADER ================= --}}
<section class="booking-header">
    <h1>Booking Confirmed</h1>
    <p class="booking-reference">
        Reference: <strong>{{ $booking->reference }}</strong>
    </p>
</section>

{{-- ================= SUMMARY LAYOUT ================= --}}
<section class="booking-layout">

    {{-- ================= LEFT: DETAILS ================= --}}
    <div class="booking-details">

        <h2>Guest Information</h2>

        <div class="detail-row">
            <span>Name</span>
            <strong>{{ $booking->guest_name }}</strong>
        </div>

        <div class="detail-row">
            <span>Email</span>
            <strong>{{ $booking->guest_email }}</strong>
        </div>

        @if($booking->guest_contact)
            <div class="detail-row">
                <span>Contact</span>
                <strong>{{ $booking->guest_contact }}</strong>
            </div>
        @endif

        @if($booking->remarks)
            <div class="detail-block">
                <span>Special Requests</span>
                <p>{{ $booking->remarks }}</p>
            </div>
        @endif

        <h2 style="margin-top:3rem;">Stay Details</h2>

        <div class="detail-row">
            <span>Room</span>
            <strong>{{ $booking->room->name }}</strong>
        </div>

        <div class="detail-row">
            <span>Check-in</span>
            <strong>{{ $booking->start_date->format('F j, Y') }}</strong>
        </div>

        <div class="detail-row">
            <span>Check-out</span>
            <strong>{{ $booking->end_date->format('F j, Y') }}</strong>
        </div>

        <div class="detail-row">
            <span>Guests</span>
            <strong>{{ $booking->number_of_guests }}</strong>
        </div>

    </div>

    {{-- ================= RIGHT: PRICE ================= --}}
    <aside class="booking-summary">

        <h3>Booking Summary</h3>

        @php
            $nights = $booking->start_date->diffInDays($booking->end_date);
        @endphp

        <div class="summary-item">
            <span>{{ $nights }} night{{ $nights > 1 ? 's' : '' }}</span>
            <strong>₱{{ number_format($booking->total_price) }}</strong>
        </div>

        <hr>

        <div class="summary-total">
            <span>Total Paid</span>
            <strong>₱{{ number_format($booking->total_price) }}</strong>
        </div>

        <p class="booking-note">
            A confirmation email has been sent to
            <strong>{{ $booking->guest_email }}</strong>.
        </p>

        <a href="{{ route('hotel.rooms') }}"
           class="book-room-btn"
           style="display:block; text-align:center; margin-top:2rem;">
            Back to Rooms
        </a>

    </aside>

</section>

{{-- ================= STYLES ================= --}}
@push('styles')
<style>
/* ================= BOOKING SUMMARY ================= */

.booking-header {
    padding: 4rem 9rem 2rem;
}

.booking-header h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 3rem;
}

.booking-reference {
    margin-top: 0.5rem;
    font-size: 0.95rem;
    color: rgba(0,0,0,0.6);
}

.booking-layout {
    padding: 3rem 9rem 6rem;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 5rem;
}

.booking-details h2 {
    margin-bottom: 1.5rem;
    font-size: 1.4rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.detail-block {
    margin-top: 1.5rem;
}

.detail-block span {
    display: block;
    font-size: 0.75rem;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.6);
    margin-bottom: 0.5rem;
}

/* ================= SUMMARY ================= */

.booking-summary {
    border-left: 1px solid rgba(0,0,0,0.1);
    padding-left: 3rem;
}

.booking-summary h3 {
    margin-bottom: 2rem;
    font-size: 1.2rem;
}

.summary-item,
.summary-total {
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
}

.summary-total {
    margin-top: 1.5rem;
    font-size: 1.3rem;
    font-weight: 600;
}
</style>
@endpush

</x-customer.layout>
