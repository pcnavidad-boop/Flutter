<x-customer.layout title="Our Rooms">

<section class="rooms-header">
    <h1 class="rooms-title">Our Rooms</h1>
    <p class="rooms-subtitle">
        Thoughtfully designed spaces for every kind of stay
    </p>
</section>

<section class="rooms-filters">

    <div class="filter-group">
        <label class="filter-label">Room Type</label>
        <select id="filter-type" class="filter-select">
            <option value="">All</option>
            <option>Single</option>
            <option>Double</option>
            <option>Quad</option>
            <option>Family</option>
            <option>Suite</option>
            <option>Penthouse</option>
            <option>Function</option>
        </select>
    </div>

    <div class="filter-group">
        <label class="filter-label">Guests</label>
        <select id="filter-guests" class="filter-select">
            <option value="">Any</option>
            <option value="1-2">1–2</option>
            <option value="3-4">3–4</option>
            <option value="5+">5+</option>
        </select>
    </div>

    <div class="filter-group">
        <label class="filter-label">Price</label>
        <select id="filter-price" class="filter-select">
            <option value="">Any</option>
            <option value="under-5000">Under ₱5,000</option>
            <option value="5000-10000">₱5,000 – ₱10,000</option>
            <option value="10000+">₱10,000+</option>
        </select>
    </div>

</section>

<div class="section-divider"></div>

<div id="no-rooms-message" class="no-rooms-message" style="display: none;">
    No rooms match your filters.
</div>

<section class="rooms-list">

    @foreach ($rooms as $room)
        <article class="room-card {{ $loop->even ? 'reverse' : '' }}"
            data-id="{{ $room->id }}"
            data-type="{{ strtolower($room->room_type) }}"
            data-capacity="{{ $room->capacity }}"
            data-price="{{ $room->base_price }}">

            <div class="room-image">
                <img src="{{ $room->image
                        ? asset('storage/' . $room->image)
                        : asset('images/placeholder-room.jpg') }}"
                     alt="{{ $room->name }}">
            </div>

            <div class="room-info">
                <h2 class="room-name">{{ $room->name }}</h2>

                <p class="room-description">
                    {{ Str::limit($room->description, 180) }}
                </p>

                <div class="room-meta">
                    <span>{{ $room->number_of_beds }} Beds</span>
                    <span>•</span>
                    <span>Up to {{ $room->capacity }} Guests</span>
                </div>

                <div class="room-footer">
                    <div class="room-price">
                        <span class="price-label">From</span>
                        <span class="price-amount">
                            ₱{{ number_format($room->base_price) }}
                        </span>
                        <span class="price-unit">/ night</span>
                    </div>

                    <a href="{{ route('hotel.room.show', $room) }}"
                       class="room-link">
                        View Details →
                    </a>
                </div>
            </div>

        </article>
    @endforeach

</section>

@push('styles')
<style>

/* ================= ROOMS PAGE ================= */

.rooms-header {
    padding: 6rem 9rem 3rem;
}

.rooms-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 3.6rem;
    margin-bottom: 0.8rem;
}

.rooms-subtitle {
    font-size: 1.1rem;
    color: rgba(0,0,0,0.65);
    max-width: 520px;
}

/* ================= ROOM CARDS ================= */

.rooms-list {
    padding: 3rem 9rem 6rem;
    display: flex;
    flex-direction: column;
    gap: 5rem;
}

.room-card {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 4rem;
    align-items: center;
}

.room-image img {
    width: 100%;
    height: 420px;
    object-fit: cover;
}

.room-info {
    max-width: 520px;
}

.room-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.4rem;
    margin-bottom: 1rem;
}

.room-description {
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 1.5rem;
}

.room-meta {
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    margin-bottom: 2rem;
    color: rgba(0,0,0,0.7);
}

.room-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.room-price {
    font-size: 1rem;
}

.price-amount {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0 0.2rem;
}

.room-link {
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-size: 0.85rem;
    color: var(--espresso);
    text-decoration: none;
    position: relative;
}

.room-link::after {
    content: "";
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 1px;
    background: var(--brass);
    transition: width .3s ease;
}

.room-link:hover::after {
    width: 100%;
}

/* Alternating layout */
.room-card.reverse {
    grid-template-columns: 1fr 1.2fr;
}

.room-card.reverse .room-image {
    order: 2;
}

.room-card.reverse .room-info {
    order: 1;
}

/* ================= NO ROOMS MESSAGE ================= */

.no-rooms-message {
    padding: 4rem 9rem;
    text-align: center;
    font-size: 1.1rem;
    color: rgba(0,0,0,0.6);
    font-family: 'Inter', sans-serif;
}

</style>
@endpush

@push('scripts')
<script src="{{ asset('customer/js/pages/rooms.js') }}"></script>
@endpush

</x-customer.layout>
