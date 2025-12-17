// common.js - shared helpers for room bookings
(function (root) {

    const Common = {

        qs(selector, rootEl = document) {
            return rootEl.querySelector(selector);
        },

        qsa(selector, rootEl = document) {
            return Array.from(rootEl.querySelectorAll(selector));
        },

        toggleDisplay(id, show) {
            const el = document.getElementById(id);
            if (el) el.style.display = show ? "block" : "none";
        },

        setText(id, text) {
            const el = document.getElementById(id);
            if (el) el.innerText = text ?? "";
        },

        formatDate(str) {
            if (!str) return "";
            const d = new Date(str);
            return d.toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "2-digit"
            });
        },

        capitalizeWords(str) {
            return str
                ?.replace(/_/g, " ")
                ?.replace(/\b\w/g, c => c.toUpperCase()) || "";
        },

        statusBadge(s) {
            return {
                pending:      "bg-warning text-dark",
                cancelled:    "bg-secondary",
                confirmed:    "bg-success",
                checked_in:   "bg-info text-dark",
                checked_out:  "bg-dark",
            }[s] ?? "bg-light text-dark";
        },

        paymentBadge(s) {
            return {
                unpaid:       "bg-secondary",
                downpayment:  "bg-info text-dark",
                fully_paid:   "bg-success",
                refunded:     "bg-danger",
            }[s] ?? "bg-light text-dark";
        }
    };

    root.AdminRoomBookingCommon = Common;

})(window);
