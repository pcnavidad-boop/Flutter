// common.js - Shared helpers for Service Bookings
(function (root) {

    const Common = {

        // ✔ Services that require time slot scheduling
        requiresTimeBased: ["spa", "restaurant", "bar"],

        /**
         * Toggle time slot UI section for add/edit booking modals.
         * @param {string} prefix - "add" or "edit"
         * @param {string} type - the service_type ("spa", "gym", "bar", etc.)
         */
        toggleTimeSlot(prefix, type) {
            const needsTime = Common.requiresTimeBased.includes(type);

            const wrapper = document.getElementById(`${prefix}-time-slot-group`);
            const startInput = document.getElementById(`${prefix}-start-time`);
            const endInput = document.getElementById(`${prefix}-end-time`);
            const hint = document.getElementById(`${prefix}-time-hint`);

            if (!wrapper || !startInput) return;

            if (needsTime) {
                // ✔ Show time slot block
                wrapper.style.display = "block";
                startInput.required = true;
            } else {
                // ❌ Hide time slot block
                wrapper.style.display = "none";

                // Clear values
                startInput.value = "";
                if (endInput) endInput.value = "";

                startInput.required = false;
            }

            // Update hint if element exists
            if (hint) {
                if (needsTime) {
                    hint.textContent = "Hourly slots only (1-hour duration)";
                } else {
                    hint.textContent = "";
                }
            }
        },

        qs(selector, base = document) {
            return base.querySelector(selector);
        },

        qsa(selector, base = document) {
            return Array.from(base.querySelectorAll(selector));
        },

        toggle(id, show) {
            const el = document.getElementById(id);
            if (el) el.style.display = show ? "block" : "none";
        },

        setText(id, value) {
            const el = document.getElementById(id);
            if (el) el.innerText = value ?? "";
        },

        formatDate(str) {
            if (!str) return "—";
            const d = new Date(str);
            return d.toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "2-digit"
            });
        },

        formatTime(str) {
            if (!str) return "—";
            return str.slice(0, 5); // HH:mm
        },

        capitalize(str) {
            return str?.replace(/_/g, " ")
                .replace(/\b\w/g, c => c.toUpperCase()) ?? "";
        },

        statusBadge(s) {
            return {
                pending: "bg-warning text-dark",
                confirmed: "bg-info text-dark",
                completed: "bg-success",
                cancelled: "bg-secondary",
            }[s] ?? "bg-light text-dark";
        },

        paymentBadge(s) {
            return {
                unpaid: "bg-secondary",
                downpayment: "bg-info text-dark",
                fully_paid: "bg-success",
                refunded: "bg-danger",
            }[s] ?? "bg-light text-dark";
        }
    };

    root.AdminServiceBookingCommon = Common;

})(window);
