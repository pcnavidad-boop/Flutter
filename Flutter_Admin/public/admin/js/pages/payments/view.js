(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("viewPaymentModal");
        if (!modal) return;

        modal.addEventListener("show.bs.modal", event => {

            const id = event.relatedTarget?.getAttribute("data-id");
            if (!id) return;

            const loading = document.getElementById("payment-view-loading");
            const content = document.getElementById("payment-view-content");
            const error   = document.getElementById("payment-view-error");

            loading.style.display = "block";
            content.style.display = "none";
            error.style.display   = "none";

            fetch(`/admin/payments/${id}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {

                    document.getElementById("pv-reference").innerText    = d.reference;
                    document.getElementById("pv-booking-ref").innerText  = d.booking.reference;
                    document.getElementById("pv-booking-type").innerText =
                        d.booking.type === "RoomBooking" ? "Room" : "Service";

                    document.getElementById("pv-amount").innerText = Number(d.amount).toFixed(2);
                    document.getElementById("pv-method").innerText =
                        d.method.replace(/_/g, " ").toUpperCase();

                    document.getElementById("pv-channel").innerText =
                        d.channel.charAt(0).toUpperCase() + d.channel.slice(1);

                    const status = document.getElementById("pv-status");
                    status.innerText = d.status.charAt(0).toUpperCase() + d.status.slice(1);
                    status.className = "badge " + (d.status === "completed" ? "bg-success" : "bg-danger");

                    document.getElementById("pv-processor").innerText = d.processor ?? "System";
                    document.getElementById("pv-paid-at").innerText   = d.paid_at;

                    loading.style.display = "none";
                    content.style.display = "block";
                })
                .catch(() => {
                    loading.style.display = "none";
                    error.style.display   = "block";
                });
        });

    });

})();
