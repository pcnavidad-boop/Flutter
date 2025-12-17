document.addEventListener('DOMContentLoaded', () => {
    const typeFilter = document.getElementById('filter-service-type');
    const timeFilter = document.getElementById('filter-time');
    const serviceCards = document.querySelectorAll('.service-card');
    const noServicesMessage = document.getElementById('no-services-message');

    /* ---------- Time utilities ---------- */

    function timeToMinutes(time) {
        if (!time) return null;
        const [h, m] = time.split(':').map(Number);
        return h * 60 + m;
    }

    function getFilterRange(period) {
        switch (period) {
            case 'morning':
                return [60, 719];     // 01:00 – 11:59
            case 'afternoon':
                return [720, 1019];   // 12:00 – 16:59
            case 'evening':
                return [1020, 1439];  // 17:00 – 23:59
            default:
                return null;
        }
    }

    function overlaps(startA, endA, startB, endB) {
        return startA <= endB && endA >= startB;
    }

    /* ---------- Core filtering ---------- */

    function applyFilters() {
        const typeValue = typeFilter.value;
        const timeValue = timeFilter.value;

        let visibleCards = [];

        serviceCards.forEach(card => {
            const serviceType = card.dataset.type;
            const startTime = card.dataset.start;
            const endTime = card.dataset.end;

            let visible = true;

            /* Service type filter */
            if (typeValue && serviceType !== typeValue) {
                visible = false;
            }

            /* Time-of-day filter (overlap-based) */
            if (visible && timeValue && startTime && endTime) {
                const serviceStart = timeToMinutes(startTime);
                const serviceEnd = timeToMinutes(endTime);
                const filterRange = getFilterRange(timeValue);

                if (filterRange) {
                    const [filterStart, filterEnd] = filterRange;
                    if (!overlaps(serviceStart, serviceEnd, filterStart, filterEnd)) {
                        visible = false;
                    }
                }
            }

            card.style.display = visible ? 'grid' : 'none';

            if (visible) {
                visibleCards.push(card);
            }
        });

        /* ---------- Dynamic alternation ---------- */

        visibleCards.forEach((card, index) => {
            card.classList.remove('reverse');

            // Even index (1-based visual order)
            if ((index + 1) % 2 === 0) {
                card.classList.add('reverse');
            }
        });

        /* ---------- No services message ---------- */

        noServicesMessage.style.display =
            visibleCards.length === 0 ? 'block' : 'none';
    }

    /* ---------- Event bindings ---------- */

    typeFilter.addEventListener('change', applyFilters);
    timeFilter.addEventListener('change', applyFilters);
});
