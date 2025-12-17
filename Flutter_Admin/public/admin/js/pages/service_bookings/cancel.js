document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modalCancelServiceBooking");
    if (!modal) return;

    const form = modal.querySelector("form");
    const refEl = modal.querySelector("#cancelServiceBookingRef");
    const warning = modal.querySelector("#cancel-payment-warning");
    const submitBtn = modal.querySelector("#btnConfirmCancelService");

    modal.addEventListener("show.bs.modal", e => {
        const btn = e.relatedTarget;
        if (!btn) return;

        // RESET MODAL STATE
        warning.classList.add("d-none");
        submitBtn.disabled = false;

        const id  = btn.getAttribute("data-id");
        const ref = btn.getAttribute("data-ref");
        const hasPayments = btn.getAttribute("data-has-payments") === "1";

        refEl.innerText = ref ?? "—";
        form.action = `/admin/service-bookings/${id}/cancel`;

        if (hasPayments) {
            warning.classList.remove("d-none");
            submitBtn.disabled = true;
        }
    });

});
