document.addEventListener('DOMContentLoaded', () => {
    const typeFilter  = document.getElementById('filter-type');
    const guestFilter = document.getElementById('filter-guests');
    const priceFilter = document.getElementById('filter-price');

    const roomCards = document.querySelectorAll('.room-card');
    const noRoomsMessage = document.getElementById('no-rooms-message');

    function applyFilters() {
        const typeValue  = typeFilter.value.toLowerCase();
        const guestValue = guestFilter.value;
        const priceValue = priceFilter.value;

        let visibleCards = [];

        roomCards.forEach(card => {
            const roomType = card.dataset.type;
            const capacity = parseInt(card.dataset.capacity);
            const price    = parseInt(card.dataset.price);

            let visible = true;

            /* Room type */
            if (typeValue && roomType !== typeValue) {
                visible = false;
            }

            /* Guests */
            if (visible && guestValue) {
                if (guestValue === '1-2' && capacity > 2) visible = false;
                if (guestValue === '3-4' && (capacity < 3 || capacity > 4)) visible = false;
                if (guestValue === '5+' && capacity < 5) visible = false;
            }

            /* Price */
            if (visible && priceValue) {
                if (priceValue === 'under-5000' && price >= 5000) visible = false;
                if (priceValue === '5000-10000' && (price < 5000 || price > 10000)) visible = false;
                if (priceValue === '10000+' && price < 10000) visible = false;
            }

            card.style.display = visible ? 'grid' : 'none';

            if (visible) visibleCards.push(card);
        });

        /* Dynamic alternation */
        visibleCards.forEach((card, index) => {
            card.classList.remove('reverse');
            if ((index + 1) % 2 === 0) {
                card.classList.add('reverse');
            }
        });

        noRoomsMessage.style.display =
            visibleCards.length === 0 ? 'block' : 'none';
    }

    [typeFilter, guestFilter, priceFilter].forEach(el =>
        el.addEventListener('change', applyFilters)
    );
});
