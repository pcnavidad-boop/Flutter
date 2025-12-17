<x-customer.layout title="Hotel Crepúsculo">

{{-- HERO --}}
<section class="hero-section">
    <img src="{{ asset('images/hero.jpg') }}" class="hero-bg">
    <div class="hero-overlay">
        <h1 class="hero-title">
            Modern Day Palace<br>
            With Classic Elegance
        </h1>
    </div>
</section>

{{-- STAY PICKER --}}
<section class="stay-picker">
    <h4 class="stay-title">STAY WITH US</h4>

    <form method="GET"
          id="stay-form"
          action="#"
          class="stay-form">

        {{-- DATE RANGE --}}
        <div class="stay-field">
            <span class="stay-label">Arrival → Departure</span>
            <input type="text"
                   id="stay-dates"
                   name="date_range"
                   placeholder="Start Date – End Date"
                   readonly>
        </div>

        {{-- GUESTS --}}
        <div class="stay-field">
            <span class="stay-label">No. of Guests</span>
            <input type="number"
                   id="stay-guests"
                   name="guests"
                   min="1"
                   value="1">
        </div>

        {{-- ROOMS --}}
        <div class="stay-field">
            <span class="stay-label">Rooms</span>
            <select id="stay-room"
                    name="room"
                    disabled>
                <option value="">Select dates & guests</option>
            </select>
        </div>

        {{-- SUBMIT --}}
        <button type="submit"
                id="stay-submit"
                class="stay-btn"
                disabled>
            Book Room
        </button>

    </form>
</section>

{{-- ABOUT --}}
<section id="about" class="about-section">

    <div class="about-content">
        <div>
            <h6 class="about-subtitle">AT A GLANCE</h6>
            <h2 class="about-title">Hotel Crepúsculo</h2>

            <p>
                Hotel Crepúsculo is a modern sanctuary shaped by warmth,
                refinement, and the subtle beauty of twilight. Every space
                is designed to slow time, inviting guests into an atmosphere
                of calm and thoughtful luxury.
            </p>

            <p>
                From curated interiors to attentive service, the hotel blends
                contemporary comfort with timeless elegance. Natural tones,
                handcrafted details, and generous spaces create an environment
                that feels both intimate and expansive.
            </p>

            <p>
                Whether visiting for leisure, celebration, or quiet retreat,
                Hotel Crepúsculo offers an experience defined not by excess,
                but by balance, intention, and enduring sophistication.
            </p>
        </div>

        <div>
            <img src="{{ asset('images/about.jpg') }}" class="img-fluid">
        </div>
    </div>

</section>

<div class="section-divider"></div>

{{-- GALLERY --}}
<section id="gallery" class="gallery-section">
    <h2 class="gallery-title">Gallery</h2>

    <div class="gallery-grid">
        @for ($i = 1; $i <= 6; $i++)
            <div class="gallery-card">
                <img src="{{ asset("images/gallery_$i.jpg") }}">
            </div>
        @endfor
    </div>
</section>

{{-- CONTACT --}}
<section id="contact" class="contact-section">
    <div class="contact-inner">
        <h2>Contact Us</h2>
        <p>Hotel Crepúsculo</p>
        <p>Twilight Avenue, Sunset City</p>
        <p>Email: info@hotelcrepusculo.com</p>
        <p>Phone: +123 456 789</p>
    </div>
</section>

@push('scripts')
<script>
    flatpickr("#stay-dates", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        onClose: fetchAvailableRooms
    });

    document.getElementById('stay-guests')
        .addEventListener('change', fetchAvailableRooms);

    async function fetchAvailableRooms() {
        const dateRange = document.getElementById('stay-dates').value;
        const guests    = document.getElementById('stay-guests').value;
        const roomSel   = document.getElementById('stay-room');
        const submitBtn = document.getElementById('stay-submit');

        roomSel.innerHTML = `<option>Checking availability…</option>`;
        roomSel.disabled = true;
        submitBtn.disabled = true;

        if (!dateRange || !guests) return;

        try {
            const res = await fetch("{{ route('hotel.rooms.availability') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date_range: dateRange,
                    guests: guests
                })
            });

            const data = await res.json();
            roomSel.innerHTML = "";

            if (!data.length) {
                roomSel.innerHTML = `<option>No rooms available</option>`;
                return;
            }

            roomSel.innerHTML =
                `<option value="">Select an available room</option>`;

            data.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent =
                    `${room.name} · ₱${room.base_price} / night`;
                roomSel.appendChild(option);
            });

            roomSel.disabled = false;

        } catch (e) {
            roomSel.innerHTML = `<option>Error loading rooms</option>`;
        }
    }

    document.getElementById('stay-room')
        .addEventListener('change', function () {
            document.getElementById('stay-submit').disabled = !this.value;
        });

    document.getElementById('stay-form')
        .addEventListener('submit', function (e) {
            e.preventDefault();

            const roomId    = document.getElementById('stay-room').value;
            const dateRange = document.getElementById('stay-dates').value;
            const guests    = document.getElementById('stay-guests').value;

            if (!roomId || !dateRange || !guests) return;

            const [startDate, endDate] = dateRange.split(' to ');

            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate,
                guests: guests
            });

            window.location.href =
                `/hotel/book-room/${roomId}?${params.toString()}`;
        });
</script>
@endpush

</x-customer.layout>
