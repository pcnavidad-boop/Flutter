<x-customer.layout title="Book {{ $service->name }}">

{{-- ================= HEADER ================= --}}
<section class="room-detail-header">
    <a href="{{ route('hotel.service.show', $service) }}" class="back-link">
        ← Back to Service
    </a>
    <h1 class="room-detail-title">Complete Your Booking</h1>
</section>

{{-- ================= BOOKING LAYOUT ================= --}}
<section class="booking-layout">

    {{-- ================= LEFT: FORM ================= --}}
    <form class="booking-form"
          method="POST"
          action="{{ route('hotel.book.service.store') }}">

        @csrf

        {{-- GLOBAL ERRORS --}}
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
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        <input type="hidden" name="appointment_date" value="{{ request('appointment_date') }}">
        <input type="hidden" name="start_time" value="{{ request('start_time') }}">
        <input type="hidden" name="end_time" value="{{ request('end_time') }}">
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
            <span>Service</span>
            <strong>{{ $service->name }}</strong>
        </div>

        <div class="summary-item">
            <span>Date</span>
            <strong>{{ request('appointment_date') }}</strong>
        </div>

        @if (request('start_time') && request('end_time'))
            <div class="summary-item">
                <span>Time</span>
                <strong>{{ request('start_time') }} – {{ request('end_time') }}</strong>
            </div>
        @endif

        <div class="summary-item">
            <span>Guests</span>
            <strong>{{ request('guests') }}</strong>
        </div>

        <hr>

        <div class="summary-item">
            <span>Service Fee</span>
            <strong>₱{{ number_format($service->base_price) }}</strong>
        </div>

        <div class="summary-total">
            <span>Total</span>
            <strong>₱{{ number_format($service->base_price) }}</strong>
        </div>

        <p class="booking-note">
            Final pricing will be confirmed upon submission.
        </p>

    </aside>

</section>

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

        hint.textContent = valid ? "Looks good" : "Invalid email format";
        hint.className   = "validation-hint " + (valid ? "valid" : "invalid");
    });
});
</script>
@endpush

</x-customer.layout>
