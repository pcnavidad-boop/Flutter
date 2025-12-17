document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modalCancelRoomBooking");
    if (!modal) return;

    const form = modal.querySelector("form");
    const refEl = modal.querySelector("#cancelBookingRef");
    const warning = modal.querySelector("#cancel-payment-warning");
    const submitBtn = modal.querySelector("#btnConfirmCancel");

    modal.addEventListener("show.bs.modal", e => {
        const btn = e.relatedTarget;
        if (!btn) return;

        // RESET MODAL STATE (important)
        warning.classList.add("d-none");
        submitBtn.disabled = false;

        const id  = btn.getAttribute("data-id");
        const ref = btn.getAttribute("data-ref");
        const hasPayments = btn.getAttribute("data-has-payments") === "1";

        refEl.innerText = ref ?? "—";
        form.action = `/admin/room-bookings/${id}/cancel`;

        if (hasPayments) {
            warning.classList.remove("d-none");
            submitBtn.disabled = true;
        }
    });

});
