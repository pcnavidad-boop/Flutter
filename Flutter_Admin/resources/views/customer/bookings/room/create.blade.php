<x-customer.layout title="Book {{ $room->name }}">

{{-- ================= HEADER ================= --}}
<section class="room-detail-header">
    <a href="{{ route('hotel.room.show', $room) }}" class="back-link">
        ← Back to Room
    </a>
    <h1 class="room-detail-title">Complete Your Booking</h1>
</section>

{{-- ================= BOOKING LAYOUT ================= --}}
<section class="booking-layout">

    {{-- ================= LEFT: FORM ================= --}}
    <form class="booking-form"
          method="POST"
          action="{{ route('hotel.book.room.store') }}">

        @csrf

        {{-- GLOBAL ERRORS (Admin parity) --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Hidden system fields --}}
        <input type="hidden" name="room_id" value="{{ $room->id }}">
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        <input type="hidden" name="number_of_guests" value="{{ request('guests') }}">

        <h2>Guest Information</h2>

        {{-- Guest Name --}}
        <div class="availability-group">
            <label>Full Name</label>
            <input type="text"
                   name="guest_name"
                   value="{{ old('guest_name') }}"
                   required
                   placeholder="John Doe">
        </div>

        {{-- Guest Email --}}
        <div class="availability-group">
            <label>Email Address</label>
            <input type="email"
                   id="guest-email"
                   name="guest_email"
                   value="{{ old('guest_email') }}"
                   required
                   placeholder="john@email.com">
            <div id="email-hint" class="validation-hint"></div>
        </div>

        {{-- Guest Contact --}}
        <div class="availability-group">
            <label>Phone Number</label>
            <input type="text"
                   name="guest_contact"
                   maxlength="11"
                   value="{{ old('guest_contact') }}"
                   placeholder="09XXXXXXXXX"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);">
        </div>

        {{-- Remarks --}}
        <div class="availability-group">
            <label>Special Requests (optional)</label>
            <textarea name="remarks"
                      rows="4"
                      placeholder="Any special requests or notes…">{{ old('remarks') }}</textarea>
        </div>

        {{-- CTA --}}
        <button type="submit" class="book-room-btn">
            Create Booking
        </button>

    </form>

    {{-- ================= RIGHT: SUMMARY ================= --}}
    <aside class="booking-summary">

        <h3>Booking Summary</h3>

        <div class="summary-item">
            <span>Room</span>
            <strong>{{ $room->name }}</strong>
        </div>

        <div class="summary-item">
            <span>Check-in</span>
            <strong>{{ request('start_date') }}</strong>
        </div>

        <div class="summary-item">
            <span>Check-out</span>
            <strong>{{ request('end_date') }}</strong>
        </div>

        <div class="summary-item">
            <span>Guests</span>
            <strong>{{ request('guests') }}</strong>
        </div>

        <hr>

        @php
            $start = \Carbon\Carbon::parse(request('start_date'));
            $end   = \Carbon\Carbon::parse(request('end_date'));
            $nights = max($start->diffInDays($end), 1);
            $subtotal = $nights * $room->base_price;
        @endphp

        <div class="summary-item">
            <span>{{ $nights }} night{{ $nights > 1 ? 's' : '' }} × ₱{{ number_format($room->base_price) }}</span>
            <strong>₱{{ number_format($subtotal) }}</strong>
        </div>

        <div class="summary-total">
            <span>Total</span>
            <strong>₱{{ number_format($subtotal) }}</strong>
        </div>

        <p class="booking-note">
            Final pricing will be confirmed upon submission.
        </p>

    </aside>

</section>

{{-- ================= STYLES ================= --}}
@push('styles')
<style>
/* ================= BOOKING PAGE ================= */
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

/* Admin-parity validation visuals */
.booking-form .is-valid {
    border-color: #198754;
    background-color: #f8fffb;
    box-shadow: 0 0 0 0.15rem rgba(25, 135, 84, 0.15);
}

.booking-form .is-invalid {
    border-color: #dc3545;
    background-color: #fff8f8;
    box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.15);
}

.validation-hint {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.validation-hint.valid {
    color: #198754;
}

.validation-hint.invalid {
    color: #dc3545;
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
@endpush

{{-- ================= SCRIPTS ================= --}}
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const email = document.getElementById("guest-email");
    const hint  = document.getElementById("email-hint");

    if (!email) return;

    email.addEventListener("input", () => {
        email.value = email.value.replace(/\s+/g, "").toLowerCase();

        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
        const valid = regex.test(email.value);

        email.classList.toggle("is-valid", valid);
        email.classList.toggle("is-invalid", !valid && email.value.length);

        if (!email.value) {
            hint.textContent = "";
            hint.className = "validation-hint";
            return;
        }

        if (valid) {
            hint.textContent = "Looks good";
            hint.className = "validation-hint valid";
        } else {
            hint.textContent = "Invalid email format";
            hint.className = "validation-hint invalid";
        }
    });
});
</script>
@endpush

</x-customer.layout>
