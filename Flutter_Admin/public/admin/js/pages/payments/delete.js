(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalDeletePayment");
        if (!modal) return;

        modal.addEventListener("show.bs.modal", event => {

            const button = event.relatedTarget;
            const id  = button.getAttribute("data-id");
            const ref = button.getAttribute("data-ref");

            const form = document.getElementById("formDeletePayment");
            const refLabel = document.getElementById("deletePaymentReference");

            form.action = `/admin/payments/${id}`;
            refLabel.innerText = ref ?? "—";
        });

    });

})();
