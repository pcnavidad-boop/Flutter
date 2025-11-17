@extends('customer.layouts.hotel')

@section('content')

<div class="container my-5" style="max-width: 600px;">

    <div class="card shadow-lg border-0 p-4"
         style="background: #F3E6D6; border-radius: 16px;">

        <h3 class="fw-bold mb-4 text-center" style="color:#7C4A3A;">
            Book a Room
        </h3>

        <form>

            <!-- Full Name -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#7C4A3A;">Full Name</label>
                <input type="text" class="form-control p-3"
                       placeholder="Enter your full name"
                       style="border-radius: 12px;">
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#7C4A3A;">Email Address</label>
                <input type="email" class="form-control p-3"
                       placeholder="Enter your email"
                       style="border-radius: 12px;">
            </div>

            <!-- Contact No. -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#7C4A3A;">Contact No.</label>
                <input type="text" class="form-control p-3"
                       placeholder="Enter your contact number"
                       style="border-radius: 12px;">
            </div>

            <!-- Date Range -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#7C4A3A;">Stay Duration</label>
                <input type="text" id="date_range" class="form-control p-3"
                       placeholder="Select check-in & check-out"
                       style="border-radius: 12px;">
            </div>

            <!-- Guests -->

            <div class="col">
                <label class="form-label fw-semibold" style="color:#7C4A3A;">Guests</label>
                <input type="number" class="form-control p-3"
                       value="0" min="1"
                       style="border-radius: 12px;">
            </div>


            <!--<div class="col">
                    <label class="form-label fw-semibold" style="color:#7C4A3A;">N</label>
                    <input type="number" class="form-control p-3"
                           value="1" min="1"
                           style="border-radius: 12px;">
            </div> -->


            <!-- Submit Button -->
            <button class="btn w-100 py-3 fw-bold mt-3"
                    style="background:#E9A46F; color:#7C4A3A; border-radius:12px;">
                Submit Booking
            </button>

        </form>
    </div>

</div>

@endsection

@push('scripts')
<script>
    flatpickr("#date_range", {
        mode: "range",
        dateFormat: "M d, Y",
        minDate: "today",
        allowInput: true,
    });
</script>
@endpush
