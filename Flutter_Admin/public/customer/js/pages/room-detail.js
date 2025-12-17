document.addEventListener('DOMContentLoaded', () => {
    console.log('Room detail JS loaded');
    
    const dateInput  = document.getElementById('stay-dates');
    const guestInput = document.getElementById('guests');
    const button     = document.getElementById('book-room-btn');
    const message    = document.getElementById('availability-message');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!csrfToken) {
        console.error('CSRF token not found!');
        message.textContent = 'Error: Security token missing.';
        return;
    }

    const roomId = parseInt(
        document.querySelector('[data-room-id]')?.dataset.roomId
    );

    const maxGuests = parseInt(guestInput.getAttribute('max'));

    /* ---------- Guard ---------- */
    if (!roomId) {
        console.error('Room ID not found for availability check.');
        return;
    }

    /* ---------- Enforce guest limits ---------- */
    guestInput.addEventListener('input', () => {
        if (guestInput.value > maxGuests) {
            guestInput.value = maxGuests;
        }
        if (guestInput.value < 1) {
            guestInput.value = 1;
        }
    });

    /* ---------- Availability check ---------- */
    async function checkAvailability() {
        console.log('Checking availability...');
        
        // Reset UI
        button.disabled = true;
        button.textContent = 'Select Dates';
        message.style.color = '';

        if (!dateInput.value || !guestInput.value) {
            message.textContent =
                'Select valid dates and guest count to check availability.';
            return;
        }

        // FIX: Parse both range format "2025-12-21 to 2025-12-25" and single date "2025-12-21"
        let startDate, endDate;
        
        if (dateInput.value.includes(' to ')) {
            // Range format
            const dates = dateInput.value.split(' to ');
            if (dates.length !== 2) {
                message.textContent = 'Please select a valid date range.';
                return;
            }
            [startDate, endDate] = dates;
        } else {
            // Single date format (for function rooms)
            startDate = dateInput.value;
            endDate = dateInput.value;
        }

        // Guest validation
        if (parseInt(guestInput.value) > maxGuests) {
            message.textContent =
                `Maximum allowed guests for this room is ${maxGuests}.`;
            message.style.color = 'red';
            button.textContent = 'Not Available';
            return;
        }

        console.log('Sending request:', { startDate, endDate, guests: guestInput.value });

        try {
            const response = await fetch('/hotel/rooms/availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    room_id: roomId,
                    start_date: startDate,
                    end_date: endDate,
                    guests: parseInt(guestInput.value)
                })
            });

            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`Server responded with ${response.status}`);
            }

            const availableRooms = await response.json();

            const isAvailable = Array.isArray(availableRooms)
                && availableRooms.some(r => r.id === roomId);

            if (isAvailable) {
                button.disabled = false;
                button.textContent = 'Book This Room';
                message.textContent =
                    'Room is available for your selected dates.';
                message.style.color = 'green';

                button.onclick = () => {
                    const params = new URLSearchParams({
                        room_id: roomId,
                        start_date: startDate,
                        end_date: endDate,
                        guests: guestInput.value
                    });

                    window.location.href =
                        `/hotel/book-room/${roomId}?${params.toString()}`;
                };
            } else {
                button.disabled = true;
                button.textContent = 'Not Available';
                message.textContent =
                    'This room is not available for the selected dates.';
                message.style.color = 'red';
            }

        } catch (error) {
            console.error('Availability check failed:', error);
            message.textContent =
                'Unable to check availability. Please try again.';
            message.style.color = 'red';
        }
    }

    /* ---------- Events ---------- */
    dateInput.addEventListener('change', checkAvailability);
    guestInput.addEventListener('change', checkAvailability);
    guestInput.addEventListener('input', checkAvailability);

    // Initial check if fields are pre-filled
    if (dateInput.value && guestInput.value) {
        checkAvailability();
    }
});