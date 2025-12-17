<x-customer.layout title="Our Services">

<section class="services-header">
    <h1 class="services-title">Our Services</h1>
    <p class="services-subtitle">
        Thoughtfully curated experiences to complement your stay
    </p>
</section>

<section class="services-filters">

    <div class="filter-group">
        <label class="filter-label">Service Type</label>
        <select id="filter-service-type" class="filter-select">
            <option value="">All</option>
            <option value="spa">Spa</option>
            <option value="restaurant">Restaurant</option>
            <option value="pool">Pool</option>
            <option value="gym">Gym</option>
            <option value="bar">Bar</option>
        </select>
    </div>

    <div class="filter-group">
        <label class="filter-label">Time of Day</label>
        <select id="filter-time" class="filter-select">
            <option value="">Any</option>
            <option value="morning">Morning (01:00–11:59)</option>
            <option value="afternoon">Afternoon (12:00–16:59)</option>
            <option value="evening">Evening (17:00–23:59)</option>
        </select>
    </div>

</section>

<div class="section-divider"></div>

<div id="no-services-message" class="no-services-message" style="display:none;">
    No services match your filters.
</div>

<section class="services-list">

    @foreach ($services as $service)
        <article class="service-card {{ $loop->even ? 'reverse' : '' }}"
            data-type="{{ strtolower($service->service_type) }}"
            data-start="{{ $service->start_time }}"
            data-end="{{ $service->end_time }}">

            <div class="service-image">
                <img src="{{ $service->image
                        ? asset('storage/' . $service->image)
                        : asset('images/placeholder-service.jpg') }}"
                     alt="{{ $service->name }}">
            </div>

            <div class="service-info">
                <h2 class="service-name">{{ $service->name }}</h2>

                <p class="service-description">
                    {{ Str::limit($service->description, 170) }}
                </p>

                <div class="service-meta">
                    <span>{{ ucfirst($service->service_type) }}</span>
                    <span>•</span>
                    <span>{{ $service->location }}</span>
                </div>

                @if ($service->start_time && $service->end_time)
                    <div class="service-schedule">
                        Available {{ $service->start_time }} – {{ $service->end_time }}
                    </div>
                @endif

                <div class="service-footer">
                    <div class="service-price">
                        <span class="price-label">From</span>
                        <span class="price-amount">
                            ₱{{ number_format($service->base_price) }}
                        </span>
                        <span class="price-unit">
                            / {{ str_replace('_', ' ', $service->price_type) }}
                        </span>
                    </div>

                    <a href="{{ route('hotel.service.show', $service) }}"
                       class="service-link">
                        View Details →
                    </a>
                </div>
            </div>

        </article>
    @endforeach

</section>

@push('styles')
<style>

/* ================= SERVICES PAGE ================= */

.services-header {
    padding: 6rem 9rem 3rem;
}

.services-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 3.6rem;
    margin-bottom: 0.8rem;
}

.services-subtitle {
    font-size: 1.1rem;
    color: rgba(0,0,0,0.65);
    max-width: 540px;
}

/* ================= SERVICE CARDS ================= */

.services-list {
    padding: 3rem 9rem 6rem;
    display: flex;
    flex-direction: column;
    gap: 5rem;
}

.service-card {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.service-image img {
    width: 100%;
    height: 380px;
    object-fit: cover;
}

.service-info {
    max-width: 520px;
}

.service-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.3rem;
    margin-bottom: 1rem;
}

.service-description {
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 1.2rem;
}

.service-meta {
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    margin-bottom: 0.6rem;
    color: rgba(0,0,0,0.7);
}

.service-schedule {
    font-size: 0.9rem;
    margin-bottom: 1.8rem;
    color: rgba(0,0,0,0.65);
}

.service-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.service-price {
    font-size: 1rem;
}

.price-amount {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0 0.2rem;
}

.service-link {
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-size: 0.85rem;
    color: var(--espresso);
    text-decoration: none;
    position: relative;
}

.service-link::after {
    content: "";
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 1px;
    background: var(--brass);
    transition: width .3s ease;
}

.service-link:hover::after {
    width: 100%;
}

/* Alternating layout */
.service-card.reverse {
    grid-template-columns: 1fr 1.1fr;
}

.service-card.reverse .service-image {
    order: 2;
}

.service-card.reverse .service-info {
    order: 1;
}

/* ================= SERVICES FILTERS ================= */

.services-filters {
    padding: 0 9rem 2.5rem;
    display: flex;
    gap: 3rem;
    align-items: flex-end;
}

/* ================= NO SERVICES MESSAGE ================= */

.no-services-message {
    padding: 4rem 9rem;
    text-align: center;
    font-size: 1.1rem;
    color: rgba(0,0,0,0.6);
}

</style>
@endpush

@push('scripts')
<script src="{{ asset('customer/js/pages/services.js') }}"></script>
@endpush

</x-customer.layout>
