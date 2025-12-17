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

        <h2 style="margin-top:3rem;">Service Details</h2>

        <div class="detail-row">
            <span>Service</span>
            <strong>{{ $booking->service->name }}</strong>
        </div>

        <div class="detail-row">
            <span>Date</span>
            <strong>{{ $booking->appointment_date->format('F j, Y') }}</strong>
        </div>

        <div class="detail-row">
            <span>Time</span>
            <strong>{{ $booking->start_time }} – {{ $booking->end_time }}</strong>
        </div>

        <div class="detail-row">
            <span>Guests</span>
            <strong>{{ $booking->number_of_guests }}</strong>
        </div>

    </div>

    {{-- ================= RIGHT: PRICE ================= --}}
    <aside class="booking-summary">

        <h3>Booking Summary</h3>

        <div class="summary-item">
            <span>Service Fee</span>
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

        <a href="{{ route('hotel.services') }}"
           class="book-room-btn"
           style="display:block; text-align:center; margin-top:2rem;">
            Back to Services
        </a>

    </aside>

</section>

</x-customer.layout>
