// add.js - Add Service Booking modal logic
(function () {

    const Common = window.AdminServiceBookingCommon || {};

    function normalizeEmail(str) {
        return str.replace(/\s+/g, "").toLowerCase();
    }

    function suggestEmailFix(val) {
        const map = {
            "gmial.com": "gmail.com",
            "gamil.com": "gmail.com",
            "hotnail.com": "hotmail.com",
            "hotmai.com": "hotmail.com",
            "yaho.com": "yahoo.com",
        };

        const parts = val.split("@");
        if (parts.length !== 2) return null;

        return map[parts[1]] ?? null;
    }

    function validateEmail(input, hintId) {
        if (!input) return;

        input.value = normalizeEmail(input.value);
        const hint = document.getElementById(hintId);
        const val = input.value;
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;

        if (!val) {
            input.classList.remove("is-valid", "is-invalid");
            if (hint) { hint.innerText = "Enter a valid email address"; hint.style.color = "#6c757d"; }
            return;
        }

        const suggestion = suggestEmailFix(val);
        if (suggestion) {
            if (hint) { hint.innerText = `Did you mean ${val.split("@")[0]}@${suggestion}?`; hint.style.color = "#6c757d"; }
            input.classList.remove("is-valid", "is-invalid");
            return;
        }

        if (!regex.test(val)) {
            input.classList.add("is-invalid");
            input.classList.remove("is-valid");
            if (hint) { hint.innerText = "Invalid email format"; hint.style.color = "#d9534f"; }
        } else {
            input.classList.add("is-valid");
            input.classList.remove("is-invalid");
            if (hint) { hint.innerText = "Looks good"; hint.style.color = "#198754"; }
        }
    }

    function populateTimeSlots(serviceId) {
        const select = document.getElementById('add-start-time');
        if (!select) return;

        // Clear existing options
        select.innerHTML = '<option value="">— Select Time Slot —</option>';

        if (!serviceId) return;

        // Get service data from the select option
        const serviceSelect = document.getElementById('add-service-id');
        const selectedOption = serviceSelect?.options[serviceSelect.selectedIndex];
        
        if (!selectedOption) return;

        const startTime = selectedOption.dataset.startTime;
        const endTime = selectedOption.dataset.endTime;
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
        const hint = document.getElementById('add-time-hint');
        if (hint) {
            if (['spa', 'restaurant', 'bar'].includes(serviceType)) {
                hint.textContent = 'Hourly slots only (1-hour duration)';
            } else {
                hint.textContent = 'Select your preferred 1-hour time slot';
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("addServiceBookingModal");
        const form = document.getElementById("formAddServiceBooking");
        const clearBtn = document.getElementById("clear-add-service-form");

        function resetForm() {
            if (!form) return;
            form.reset();

            const email = document.getElementById("add-guest-email");
            const hint = document.getElementById("add-email-hint");
            if (email) email.classList.remove("is-valid", "is-invalid");
            if (hint) { hint.innerText = "Enter a valid email address"; hint.style.color = "#6c757d"; }

            // Reset time slots
            const timeSelect = document.getElementById('add-start-time');
            if (timeSelect) timeSelect.innerHTML = '<option value="">— Select Time Slot —</option>';
            
            const endInput = document.getElementById('add-end-time');
            if (endInput) endInput.value = '';
        }

        modal?.addEventListener("show.bs.modal", resetForm);
        clearBtn?.addEventListener("click", resetForm);

        document.getElementById("add-guest-email")?.addEventListener("input", (e) => {
            validateEmail(e.target, "add-email-hint");
        });

        // Auto-calculate end time when start time changes
        document.getElementById('add-start-time')?.addEventListener('change', (e) => {
            const startTime = e.target.value;
            const endInput = document.getElementById('add-end-time');
            
            if (!startTime || !endInput) return;

            // Calculate end time (add 1 hour)
            const [hour, minute] = startTime.split(':').map(Number);
            const endHour = hour + 1;
            const endTime = `${String(endHour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            
            endInput.value = endTime;
        });

        // Populate slots when service changes
        document.getElementById('add-service-id')?.addEventListener('change', (e) => {
            const serviceId = e.target.value;
            populateTimeSlots(serviceId);
            
            // Clear date/time when switching service
            const dateInput = document.getElementById('add-appointment-date');
            const startInput = document.getElementById('add-start-time');
            const endInput = document.getElementById('add-end-time');
            
            if (dateInput) dateInput.value = '';
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';

            // Reload calendar if present
            if (window.svcAddCal && typeof window.svcAddCal.open === 'function') {
                window.svcAddCal.open();
            }
        });

        // -------------------------------
        // TIME SLOT VISIBILITY LOGIC
        // -------------------------------
        function updateAddTimeSlotVisibility() {
            const serviceSelect = document.getElementById('add-service-id');
            const group = document.getElementById('add-time-slot-group');
            const timeSelect = document.getElementById('add-start-time');

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
                document.getElementById("add-end-time").value = "";
            }
        }

        // Trigger when changing service
        document.getElementById("add-service-id")?.addEventListener("change", updateAddTimeSlotVisibility);

        // Trigger when opening modal
        modal?.addEventListener("show.bs.modal", updateAddTimeSlotVisibility);

        // Initial load
        updateAddTimeSlotVisibility();

    });

})();