// view.js - View Room Booking modal logic
(function () {

    const Common = window.AdminRoomBookingCommon;

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("viewRoomBookingModal");

        modal?.addEventListener("show.bs.modal", evt => {

            const id = evt.relatedTarget?.getAttribute("data-id");
            if (!id) return;

            Common.toggleDisplay("view-loading", true);
            Common.toggleDisplay("view-content", false);
            Common.toggleDisplay("view-error", false);

            fetch(`/admin/room-bookings/${id}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {

                    Common.setText("view-ref", data.reference);
                    Common.setText("view-guest-name", data.guest_name);
                    Common.setText("view-guest-email", data.guest_email);
                    Common.setText("view-contact", data.guest_contact ?? "—");
                    Common.setText("view-num-guests", data.number_of_guests);

                    Common.setText("view-room-name", data.room?.name ?? "—");
                    Common.setText("view-room-number", data.room?.number ?? "—");

                    Common.setText("view-start", Common.formatDate(data.start_date));
                    Common.setText("view-end", Common.formatDate(data.end_date));

                    Common.setText("view-remarks", data.remarks ?? "None");

                    Common.setText("view-created", data.created_at);
                    Common.setText("view-created-by", data.created_by ?? "—");

                    // Status badge
                    const status = document.getElementById("view-status");
                    status.innerText = Common.capitalizeWords(data.booking_status);
                    status.className = `badge px-3 py-2 fw-semibold ${Common.statusBadge(data.booking_status)}`;

                    // Payment badge
                    const payment = document.getElementById("view-payment");
                    payment.innerText = Common.capitalizeWords(data.payment_status);
                    payment.className = `badge px-3 py-2 fw-semibold ${Common.paymentBadge(data.payment_status)}`;

                    Common.setText("view-total", Number(data.total_price || 0).toFixed(2));

                    Common.toggleDisplay("view-loading", false);
                    Common.toggleDisplay("view-content", true);
                })
                .catch(() => {
                    Common.toggleDisplay("view-loading", false);
                    Common.toggleDisplay("view-error", true);
                });
        });

        /** Copy reference button */
        const copyBtn = document.getElementById("copy-ref-btn");
        copyBtn?.addEventListener("click", () => {
            const ref = document.getElementById("view-ref")?.innerText;
            if (!ref) return;

            navigator.clipboard.writeText(ref).then(() => {
                copyBtn.innerHTML = `<i class="bi bi-check2"></i>`;
                copyBtn.style.background = "#c7f5d9";
                copyBtn.style.color = "#0f5132";

                setTimeout(() => {
                    copyBtn.innerHTML = `<i class="bi bi-clipboard"></i>`;
                    copyBtn.style.background = "#e9ecef";
                    copyBtn.style.color = "";
                }, 1200);
            });
        });

    });

})();
