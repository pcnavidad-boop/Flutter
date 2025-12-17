// view.js - View Service Booking modal logic
(function () {

    const Common = window.AdminServiceBookingCommon;

    /**
     * Format time slot as range
     * @param {string} startTime - e.g., "14:00"
     * @param {string} endTime - e.g., "15:00"
     * @returns {string} - e.g., "14:00 - 15:00"
     */
    function formatTimeSlot(startTime, endTime) {
        if (!startTime) return "—";
        
        const start = Common.formatTime(startTime);
        const end = endTime ? Common.formatTime(endTime) : "—";
        
        return `${start} - ${end}`;
    }

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("viewServiceBookingModal");

        modal?.addEventListener("show.bs.modal", evt => {

            const id = evt.relatedTarget?.getAttribute("data-id");
            if (!id) return;

            // Show loading
            Common.toggle("view-loading", true);
            Common.toggle("view-content", false);
            Common.toggle("view-error", false);

            fetch(`/admin/service-bookings/${id}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {

                    // Reference block
                    Common.setText("view-ref", d.reference);

                    // Guest info
                    Common.setText("view-guest-name", d.guest_name);
                    Common.setText("view-guest-email", d.guest_email);
                    Common.setText("view-contact", d.guest_contact ?? "—");
                    Common.setText("view-num-guests", d.number_of_guests);

                    // Service info
                    Common.setText("view-service-name", d.service.name);
                    Common.setText("view-service-location", d.service.location ?? "—");
                    Common.setText("view-service-type", Common.capitalize(d.service.type ?? "—"));

                    // Appointment - use formatTimeSlot for time range display
                    Common.setText("view-date", Common.formatDate(d.appointment_date));
                    Common.setText("view-time", formatTimeSlot(d.start_time, d.end_time));

                    // Summary
                    Common.setText("view-total", Number(d.total_price || 0).toFixed(2));
                    Common.setText("view-remarks", d.remarks ?? "None");

                    // Status badges
                    const status = document.getElementById("view-status");
                    status.innerText = Common.capitalize(d.booking_status);
                    status.className = `badge px-3 py-2 fw-semibold ${Common.statusBadge(d.booking_status)}`;

                    const pay = document.getElementById("view-payment");
                    pay.innerText = Common.capitalize(d.payment_status);
                    pay.className = `badge px-3 py-2 fw-semibold ${Common.paymentBadge(d.payment_status)}`;

                    // Metadata
                    Common.setText("view-created", d.created_at);
                    Common.setText("view-created-by", d.created_by ?? "System");

                    // Show content
                    Common.toggle("view-loading", false);
                    Common.toggle("view-content", true);
                })
                .catch(() => {
                    Common.toggle("view-loading", false);
                    Common.toggle("view-error", true);
                });
        });
    });

})();