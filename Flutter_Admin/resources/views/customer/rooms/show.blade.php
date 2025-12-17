<x-customer.layout :title="$room->name">

<div data-room-id="{{ $room->id }}">

    {{-- ================= HEADER ================= --}}
    <section class="room-detail-header">
        <a href="{{ route('hotel.rooms') }}" class="back-link">← Back to Rooms</a>
        <h1 class="room-detail-title">{{ $room->name }}</h1>
    </section>

    {{-- ================= HERO IMAGE ================= --}}
    <section class="room-detail-hero">
        <img src="{{ $room->image
                ? asset('storage/' . $room->image)
                : asset('images/placeholder-room.jpg') }}"
             alt="{{ $room->name }}">
    </section>

    {{-- ================= CONTENT ================= --}}
    <section class="room-detail-content">

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="room-detail-main">

            <div class="room-detail-meta">
                <span>{{ ucfirst($room->room_type) }}</span>
                <span>•</span>
                <span>{{ $room->number_of_beds }} Beds</span>
                <span>•</span>
                <span>Up to {{ $room->capacity }} Guests</span>
            </div>

            <div class="room-detail-description">
                <p>{{ $room->description }}</p>
            </div>

        </div>

        {{-- ===== SIDEBAR / AVAILABILITY ===== --}}
        <aside class="room-detail-sidebar">

            <form id="room-availability-form" onsubmit="return false;">

                <div class="availability-group">
                    <label for="stay-dates">Check-in Date → Check-out Date</label>
                    <input type="text"
                           id="stay-dates"
                           placeholder="Select dates"
                           readonly>
                </div>

                <div class="availability-group">
                    <label for="guests">Guests</label>
                    <input type="number"
                           id="guests"
                           min="1"
                           max="{{ $room->capacity }}"
                           value="1"
                           required>
                </div>

                <div class="price-box">
                    <div class="price-label">From</div>
                    <div class="price-value">
                        ₱{{ number_format($room->base_price) }}
                        <span>
                            {{ $room->room_type === 'function' ? '/ day' : '/ night' }}
                        </span>
                    </div>
                </div>

                <button type="button"
                        id="book-room-btn"
                        class="book-room-btn"
                        disabled>
                    Select Dates
                </button>

                <p id="availability-message" class="booking-note">
                    Select valid dates and guest count to check availability.
                </p>

            </form>

        </aside>

    </section>

</div>

{{-- ================= STYLES ================= --}}
@push('styles')
<style>
/* ================= HERO ================= */

.room-detail-hero img {
    width: 100%;
    height: 520px;
    object-fit: cover;
}

/* ================= CONTENT ================= */

.room-detail-content {
    padding: 4rem 9rem 6rem;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 5rem;
}

.room-detail-meta {
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    margin-bottom: 2rem;
    color: rgba(0,0,0,0.7);
}

.room-detail-description {
    font-size: 1.05rem;
    line-height: 1.8;
    max-width: 620px;
}

/* ================= SIDEBAR ================= */

.room-detail-sidebar {
    border-left: 1px solid rgba(0,0,0,0.1);
    padding-left: 3rem;
}

/* ================= PRICE ================= */

.price-box {
    margin: 2.5rem 0;
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

</style>
@endpush

{{-- ================= SCRIPTS ================= --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if flatpickr is loaded
    if (typeof flatpickr === 'undefined') {
        console.error('Flatpickr not loaded!');
        // Load it dynamically
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        script.onload = initializeDatePicker;
        document.head.appendChild(script);
    } else {
        initializeDatePicker();
    }
    
    async function initializeDatePicker() {
        const input = document.getElementById('stay-dates');
        if (!input) {
            console.error('Input #stay-dates not found!');
            return;
        }
        
        const roomId = document.querySelector('[data-room-id]')?.dataset.roomId;
        const roomType = "{{ $room->room_type }}";
        
        if (!roomId) {
            console.error('Room ID not found');
            return;
        }

        // Track the first selected date for function rooms
        let firstSelectedDate = null;
        let isSingleDayBooking = false;

        try {
            const response = await fetch(`/hotel/rooms/${roomId}/booked-dates`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const disabledRanges = await response.json();
            console.log('Loaded disabled ranges:', disabledRanges);
            
            const baseConfig = {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: Array.isArray(disabledRanges) ? disabledRanges : [],
                
                // FIX: Custom formatter to show single date properly
                onValueUpdate: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const start = selectedDates[0];
                        const end = selectedDates[1];
                        
                        // Check if it's the same date (single day booking)
                        if (start.getTime() === end.getTime()) {
                            isSingleDayBooking = true;
                            // Display as single date
                            input.value = instance.formatDate(start, "Y-m-d");
                        } else {
                            isSingleDayBooking = false;
                            // Display as range (default behavior)
                            input.value = dateStr;
                        }
                    }
                },
                
                onChange: function(selectedDates, dateStr, instance) {
                    console.log('Dates selected:', selectedDates.length, 'dates');
                    
                    // For function rooms: track first selection
                    if (roomType === 'function') {
                        if (selectedDates.length === 1) {
                            firstSelectedDate = selectedDates[0].getTime();
                            isSingleDayBooking = false;
                        } else if (selectedDates.length === 2) {
                            // Check if user clicked the same date twice
                            if (firstSelectedDate === selectedDates[1].getTime()) {
                                console.log('Same date clicked - single day booking');
                                isSingleDayBooking = true;
                                // Keep both dates the same for validation
                                instance.setDate([selectedDates[0], selectedDates[0]], true);
                            } else {
                                isSingleDayBooking = false;
                            }
                            
                            // Trigger availability check
                            setTimeout(() => {
                                const event = new Event('change');
                                input.dispatchEvent(event);
                            }, 50);
                            
                            // Reset tracker
                            firstSelectedDate = null;
                        }
                    } else {
                        // For stay rooms: trigger when 2 dates selected
                        if (selectedDates.length === 2) {
                            const event = new Event('change');
                            input.dispatchEvent(event);
                        }
                    }
                },
                
                onClose: function(selectedDates, dateStr, instance) {
                    // FIX: Auto-complete to single day if user closed with only 1 date
                    if (roomType === 'function' && selectedDates.length === 1) {
                        const singleDate = selectedDates[0];
                        isSingleDayBooking = true;
                        instance.setDate([singleDate, singleDate], true);
                        
                        // Trigger availability check
                        setTimeout(() => {
                            const event = new Event('change');
                            input.dispatchEvent(event);
                        }, 50);
                    }
                    
                    // Reset tracker
                    firstSelectedDate = null;
                }
            };
            
            // Only add minRange for stay rooms (need at least 1 night)
            if (roomType !== 'function') {
                baseConfig.minRange = 1;
            }
            
            const flatpickrInstance = flatpickr(input, baseConfig);
            
            console.log('Flatpickr initialized successfully:', flatpickrInstance);
            
        } catch (error) {
            console.error('Error loading booked dates:', error);
            
            // Initialize without disabled dates as fallback
            const fallbackConfig = {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                
                onValueUpdate: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const start = selectedDates[0];
                        const end = selectedDates[1];
                        
                        if (start.getTime() === end.getTime()) {
                            isSingleDayBooking = true;
                            input.value = instance.formatDate(start, "Y-m-d");
                        } else {
                            isSingleDayBooking = false;
                            input.value = dateStr;
                        }
                    }
                },
                
                onChange: function(selectedDates, dateStr, instance) {
                    if (roomType === 'function') {
                        if (selectedDates.length === 1) {
                            firstSelectedDate = selectedDates[0].getTime();
                            isSingleDayBooking = false;
                        } else if (selectedDates.length === 2) {
                            if (firstSelectedDate === selectedDates[1].getTime()) {
                                isSingleDayBooking = true;
                                instance.setDate([selectedDates[0], selectedDates[0]], true);
                            } else {
                                isSingleDayBooking = false;
                            }
                            
                            setTimeout(() => {
                                const event = new Event('change');
                                input.dispatchEvent(event);
                            }, 50);
                            
                            firstSelectedDate = null;
                        }
                    } else {
                        if (selectedDates.length === 2) {
                            const event = new Event('change');
                            input.dispatchEvent(event);
                        }
                    }
                },
                
                onClose: function(selectedDates, dateStr, instance) {
                    if (roomType === 'function' && selectedDates.length === 1) {
                        const singleDate = selectedDates[0];
                        isSingleDayBooking = true;
                        instance.setDate([singleDate, singleDate], true);
                        
                        setTimeout(() => {
                            const event = new Event('change');
                            input.dispatchEvent(event);
                        }, 50);
                    }
                    firstSelectedDate = null;
                }
            };
            
            if (roomType !== 'function') {
                fallbackConfig.minRange = 1;
            }
            
            flatpickr(input, fallbackConfig);
        }
    }
});
</script>

{{-- Availability logic --}}
<script src="{{ asset('customer/js/pages/room-detail.js') }}"></script>
@endpush

</x-customer.layout>
