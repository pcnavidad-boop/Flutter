// edit.js - Edit Service Booking modal logic
(function () {

    const Common = window.AdminServiceBookingCommon || {};

    function populateTimeSlots(serviceSelect) {
        const select = document.getElementById('edit-start-time');
        if (!select) return;

        // Clear existing options
        select.innerHTML = '<option value="">— Select Time Slot —</option>';

        if (!serviceSelect || !serviceSelect.value) return;

        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        if (!selectedOption) return;

        const startTime = selectedOption.dataset.startTime; // e.g., "09:00"
        const endTime = selectedOption.dataset.endTime;     // e.g., "22:00"
        const serviceType = selectedOption.dataset.type;

        if (!startTime || !endTime) return;

        // Parse times
        const [startHour] = startTime.split(':').map(Number);
        const [endHour] = endTime.split(':').map(Number);

        // Generate hourly slots
        for (let hour = startHour; hour < endHour; hour++) {
            const slotStart = `${String(hour).padStart(2, '0')}:00`;
            const slotEnd = `${String(hour + 1).padStart(2, '0')}:00`;
            
            const option = document.createElement('option');
            option.value = slotStart;
            option.textContent = `${slotStart} - ${slotEnd}`;
            select.appendChild(option);
        }

        // Update hint based on service type
        const hint = document.getElementById('edit-time-hint');
        if (hint) {
            if (['spa', 'restaurant', 'bar'].includes(serviceType)) {
                hint.textContent = 'Hourly slots only (1-hour duration)';
            } else {
                hint.textContent = 'Select your preferred 1-hour time slot';
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById("editServiceBookingModal");
        const form = document.getElementById("edit-service-booking-form");
        const resetBtn = document.getElementById("reset-edit-service-form");

        let original = null;

        function setVal(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val ?? "";
        }

        modal?.addEventListener("show.bs.modal", evt => {
            const id = evt.relatedTarget?.getAttribute("data-id");
            if (!id) return;

            fetch(`/admin/service-bookings/${id}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {
                    original = JSON.parse(JSON.stringify(d));
                    if (form) form.action = `/admin/service-bookings/${id}`;

                    setVal("edit-guest-name", d.guest_name);
                    setVal("edit-guest-email", d.guest_email);
                    setVal("edit-guest-contact", d.guest_contact ?? "");
                    
                    const serviceSelect = document.getElementById("edit-service-id");
                    if (serviceSelect) {
                        serviceSelect.value = d.service.id;
                        
                        // Populate time slots based on selected service
                        populateTimeSlots(serviceSelect);
                        
                        // THEN set the start time value (after slots are populated)
                        setTimeout(() => {
                            setVal("edit-start-time", d.start_time ?? "");
                            setVal("edit-end-time", d.end_time ?? "");
                        }, 50);
                    }
                    
                    setVal("edit-guests", d.number_of_guests ?? 1);
                    setVal("edit-appointment-date", d.appointment_date ?? "");
                    setVal("edit-remarks", d.remarks ?? "");
                })
                .catch(() => console.error("Failed to load service booking"));
        });

        resetBtn?.addEventListener("click", () => {
            if (!original) return;
            setVal("edit-guest-name", original.guest_name);
            setVal("edit-guest-email", original.guest_email);
            setVal("edit-guest-contact", original.guest_contact ?? "");
            
            const serviceSelect = document.getElementById("edit-service-id");
            if (serviceSelect) {
                serviceSelect.value = original.service.id;
                populateTimeSlots(serviceSelect);
                
                setTimeout(() => {
                    setVal("edit-start-time", original.start_time ?? "");
                    setVal("edit-end-time", original.end_time ?? "");
                }, 50);
            }
            
            setVal("edit-guests", original.number_of_guests ?? 1);
            setVal("edit-appointment-date", original.appointment_date ?? "");
            setVal("edit-remarks", original.remarks ?? "");
        });

        // Auto-calculate end time when start time changes
        document.getElementById('edit-start-time')?.addEventListener('change', (e) => {
            const startTime = e.target.value;
            const endInput = document.getElementById('edit-end-time');
            
            if (!startTime || !endInput) return;

            // Calculate end time (add 1 hour)
            const [hour, minute] = startTime.split(':').map(Number);
            const endHour = hour + 1;
            const endTime = `${String(endHour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            
            endInput.value = endTime;
        });

        // Change time slots when service select changes
        document.getElementById("edit-service-id")?.addEventListener("change", (e) => {
            populateTimeSlots(e.target);
            
            // Clear time selections when service changes
            setVal("edit-start-time", "");
            setVal("edit-end-time", "");
        });

        // -------------------------------
        // TIME SLOT VISIBILITY LOGIC
        // -------------------------------
        function updateEditTimeSlotVisibility() {
            const serviceSelect = document.getElementById('edit-service-id');
            const group = document.getElementById('edit-time-slot-group');
            const timeSelect = document.getElementById('edit-start-time');

            if (!serviceSelect || !group || !timeSelect) return;

            const selected = serviceSelect.options[serviceSelect.selectedIndex];
            const type = selected?.dataset?.type ?? null;

            const timeBased = ['spa', 'restaurant', 'bar'].includes(type);

            group.style.display = timeBased ? "block" : "none";

            if (timeBased) {
                timeSelect.setAttribute("required", "required");
            } else {
                timeSelect.removeAttribute("required");
                timeSelect.value = "";
                document.getElementById("edit-end-time").value = "";
            }
        }

        // Trigger when service changes
        document.getElementById("edit-service-id")?.addEventListener("change", updateEditTimeSlotVisibility);

        // Trigger when modal loads data
        modal?.addEventListener("show.bs.modal", () => {
            setTimeout(updateEditTimeSlotVisibility, 80); // wait for data fill
        });

        // Initial load
        updateEditTimeSlotVisibility();

    });

})();