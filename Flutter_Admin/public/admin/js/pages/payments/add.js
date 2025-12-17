(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("addPaymentModal");
        if (!modal) return;

        /* MATCH ACTUAL BLADE ELEMENTS */
        const refInput = modal.querySelector(
            'input[name="booking_reference"]'
        );

        const amountInput = modal.querySelector(
            'input[name="amount"]'
        );

        const summaryTotal     = document.getElementById("summary-total");
        const summaryPaid      = document.getElementById("summary-paid");
        const summaryRemaining = document.getElementById("summary-remaining");
        const suggestedSpan    = document.getElementById("suggested-downpayment");

        if (!refInput) return;

        function detectType(ref) {
            if (ref.startsWith("RB-")) return "room";
            if (ref.startsWith("SB-")) return "service";
            return null;
        }

        function format(val) {
            return `₱${Number(val).toFixed(2)}`;
        }

        refInput.addEventListener("blur", () => {

            const ref = refInput.value.trim();
            const type = detectType(ref);

            // Reset UI
            summaryTotal.innerText     = format(0);
            summaryPaid.innerText      = format(0);
            summaryRemaining.innerText = format(0);
            suggestedSpan.innerText    = format(0);

            if (!ref || !type) {
                return;
            }

            fetch(`/admin/payments/summary/${type}/${ref}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {

                    summaryTotal.innerText     = format(d.total);
                    summaryPaid.innerText      = format(d.paid);
                    summaryRemaining.innerText = format(d.remaining);

                    const suggested = Math.min(
                        d.remaining,
                        +(d.total * 0.30).toFixed(2)
                    );

                    if (d.remaining > 0 && amountInput) {
                        amountInput.value = suggested;
                    }

                    suggestedSpan.innerText = format(suggested);
                })
                .catch(() => {
                    // Booking not found → keep zeros
                });
        });

    });

})();
