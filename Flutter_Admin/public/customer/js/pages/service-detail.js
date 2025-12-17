(function () {
    'use strict';

    /* ================= DOM ================= */
    const dateInput   = document.getElementById('service-date');
    const guestInput  = document.getElementById('service-guests');
    const timeSelect  = document.getElementById('service-start-time');
    const button      = document.getElementById('book-service-btn');
    const message     = document.getElementById('service-availability-message');

    const wrapper = document.querySelector('[data-service-id]');
    if (!wrapper || !dateInput || !guestInput || !button || !message) return;

    /* ================= DATA ================= */
    const serviceId   = parseInt(wrapper.dataset.serviceId);
    const serviceType = wrapper.dataset.serviceType;
    const startTime   = wrapper.dataset.startTime;
    const endTime     = wrapper.dataset.endTime;
    const capacity    = parseInt(guestInput.getAttribute('max') || 1);

    /* ================= GUEST GUARD ================= */
    guestInput.addEventListener('input', () => {
        if (guestInput.value > capacity) guestInput.value = capacity;
        if (guestInput.value < 1) guestInput.value = 1;
    });

    /* ================= BUILD SLOTS ================= */
    function buildTimeSlots(bookedMap = {}) {
        if (!timeSelect || !startTime || !endTime) return;

        timeSelect.innerHTML =
            '<option value="">— Select Time Slot —</option>';

        const startHour = parseInt(startTime.split(':')[0]);
        const endHour   = parseInt(endTime.split(':')[0]);

        for (let h = startHour; h < endHour; h++) {
            const slot = `${String(h).padStart(2, '0')}:00`;
            const next = `${String(h + 1).padStart(2, '0')}:00`;

            const bookedGuests = bookedMap[slot] || 0;
            const remaining    = capacity - bookedGuests;

            const opt = document.createElement('option');
            opt.value = slot;

            if (remaining <= 0) {
                opt.disabled = true;
                opt.textContent = `${slot} – ${next} (Fully booked)`;
            } else {
                opt.textContent =
                    `${slot} – ${next} (${remaining} left)`;
            }

            timeSelect.appendChild(opt);
        }
    }

    /* ================= FETCH SLOT USAGE ================= */
    async function fetchSlotUsage(date) {
        if (!timeSelect || !date) return {};

        try {
            const res = await fetch(
                `/hotel/services/${serviceId}/booked-slots?date=${date}`
            );

            if (!res.ok) return {};
            return await res.json(); // { "10:00": 2 }
        } catch {
            return {};
        }
    }

    /* ================= AVAILABILITY CHECK ================= */
    async function checkAvailability() {
        button.disabled = true;
        button.textContent = 'Select Date';
        message.style.color = '';

        if (!dateInput.value || !guestInput.value) {
            message.textContent =
                'Select a date and guest count to check availability.';
            return;
        }

        if (guestInput.value > capacity) {
            message.textContent =
                `Maximum allowed guests is ${capacity}.`;
            message.style.color = 'red';
            return;
        }

        // For time-based services, only check availability if time is selected
        if (timeSelect && !timeSelect.value) {
            message.textContent = 'Please select a time slot.';
            return;
        }

        try {
            const response = await fetch('/hotel/services/availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    appointment_date: dateInput.value,
                    start_time: timeSelect ? timeSelect.value : null,
                    guests: guestInput.value
                })
            });

            const available = await response.json();

            if (available === true) {
                button.disabled = false;
                button.textContent = 'Book This Service';
                message.textContent = 'Service is available.';
                message.style.color = 'green';

                button.onclick = () => {
                    const start = timeSelect ? timeSelect.value : null;
                    const end   = start
                        ? `${String(parseInt(start.split(':')[0]) + 1).padStart(2, '0')}:00`
                        : null;

                    // Build query parameters
                    const params = new URLSearchParams({
                        appointment_date: dateInput.value,
                        start_time: start || '',
                        end_time: end || '',
                        guests: guestInput.value
                    });

                    // Debug: Log the URL before redirect
                    const url = `/hotel/book-service/${serviceId}?${params.toString()}`;
                    console.log('Redirecting to:', url);
                    console.log('Service ID:', serviceId);
                    console.log('Parameters:', {
                        appointment_date: dateInput.value,
                        start_time: start,
                        end_time: end,
                        guests: guestInput.value
                    });

                    // Redirect with window.location
                    window.location.href = url;
                };

            } else {
                button.textContent = 'Not Available';
                message.textContent =
                    'This service is not available for the selected options.';
                message.style.color = 'red';
            }

        } catch {
            message.textContent =
                'Unable to check availability. Please try again.';
            message.style.color = 'red';
        }
    }

    /* ================= DATE HANDLER ================= */
    async function handleDateChange(dateStr) {
        if (!dateStr) return;

        dateInput.value = dateStr;

        if (['spa', 'restaurant', 'bar'].includes(serviceType)) {
            const slotUsage = await fetchSlotUsage(dateStr);
            buildTimeSlots(slotUsage);
            
            message.textContent = 'Please select a time slot.';
            message.style.color = '';
        } else {
            // For non-time-based services, check availability immediately
            await checkAvailability();
        }
    }

    /* ================= FLATPICKR HOOK ================= */
    window.onDateChange = handleDateChange;

    /* ================= EVENTS ================= */
    guestInput.addEventListener('change', checkAvailability);
    if (timeSelect) timeSelect.addEventListener('change', checkAvailability);

})();