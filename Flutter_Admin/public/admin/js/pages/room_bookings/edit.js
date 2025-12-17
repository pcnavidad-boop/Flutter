// edit.js - Edit Room Booking modal logic
(function () {

    const Common = window.AdminRoomBookingCommon;

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("editRoomBookingModal");
        const form = document.getElementById("edit-room-booking-form");
        const resetBtn = document.getElementById("reset-edit-form");

        let originalData = null;

        /** Helper setter */
        function setVal(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val ?? "";
        }

        modal?.addEventListener("show.bs.modal", evt => {

            const id = evt.relatedTarget?.getAttribute("data-id");
            if (!id) return;

            fetch(`/admin/room-bookings/${id}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {

                    originalData = JSON.parse(JSON.stringify(data)); // deep copy

                    if (form) form.action = `/admin/room-bookings/${id}`;

                    // Guest Info
                    setVal("edit-guest-name", data.guest_name);
                    setVal("edit-guest-email", data.guest_email);
                    setVal("edit-guest-contact", data.guest_contact);
                    setVal("edit-guests", data.number_of_guests ?? 1);

                    // Room
                    document.getElementById("edit-room-id").value = data.room?.id ?? "";

                    // Booking Type
                    const typeSel = document.getElementById("edit-type");

                    if (data.type === "website") {
                        typeSel.innerHTML = `<option value="website">Website</option>`;
                        typeSel.disabled = true;
                    } else {
                        typeSel.disabled = false;
                        typeSel.innerHTML = `
                            <option value="walk-in">Walk-in</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                        `;
                        typeSel.value = data.type ?? "walk-in";
                    }

                    // Dates
                    setVal("edit-start-date", data.start_date);
                    setVal("edit-end-date", data.end_date);

                    if (window.editCal && typeof window.editCal.fill === "function") {
                        window.editCal.fill(data.start_date, data.end_date);
                    }

                    // Remarks
                    setVal("edit-remarks", data.remarks);

                    // Reset email validation UI
                    const emailInput = document.getElementById("edit-guest-email");
                    const hint = document.getElementById("edit-email-hint");
                    if (emailInput) emailInput.classList.remove("is-valid", "is-invalid");
                    if (hint) {
                        hint.innerText = "Enter a valid email address";
                        hint.style.color = "#6c757d";
                    }
                })
                .catch(() => console.error("Failed to load booking for edit."));
        });

        /** Reset button */
        resetBtn?.addEventListener("click", () => {
            if (!originalData) return;

            const d = originalData;

            setVal("edit-guest-name", d.guest_name);
            setVal("edit-guest-email", d.guest_email);
            setVal("edit-guest-contact", d.guest_contact);
            setVal("edit-guests", d.number_of_guests);

            const roomSel = document.getElementById("edit-room-id");
            roomSel.value = d.room?.id ?? "";

            const typeSel = document.getElementById("edit-type");
            if (d.type === "website") {
                typeSel.innerHTML = `<option value="website">Website</option>`;
                typeSel.disabled = true;
            } else {
                typeSel.disabled = false;
                typeSel.innerHTML = `
                    <option value="walk-in">Walk-in</option>
                    <option value="phone">Phone</option>
                    <option value="email">Email</option>
                `;
                typeSel.value = d.type ?? "walk-in";
            }

            setVal("edit-start-date", d.start_date);
            setVal("edit-end-date", d.end_date);
            setVal("edit-remarks", d.remarks);

            if (window.editCal) {
                window.editCal.fill(d.start_date, d.end_date);
            }
        });

    });

})();
