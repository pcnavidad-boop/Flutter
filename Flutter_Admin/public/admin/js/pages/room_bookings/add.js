// add.js - Add Room Booking modal logic
(function () {

    const Common = window.AdminRoomBookingCommon;

    /** Normalize email */
    function normalizeEmailInput(input) {
        input.value = input.value.replace(/\s+/g, "").toLowerCase();
    }

    /** Suggest common email typos */
    function suggestDomainFix(email) {
        const map = {
            "gmial.com": "gmail.com",
            "gamil.com": "gmail.com",
            "hotnail.com": "hotmail.com",
            "hotmai.com": "hotmail.com",
            "yaho.com": "yahoo.com",
        };

        const parts = email.split("@");
        if (parts.length !== 2) return null;

        return map[parts[1]] ?? null;
    }

    /** Validate input email */
    function validateEmailInput(input, hintId) {
        if (!input) return;

        normalizeEmailInput(input);
        const hint = document.getElementById(hintId);
        const val = input.value;

        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;

        if (!val) {
            input.classList.remove("is-valid", "is-invalid");
            if (hint) {
                hint.innerText = "Enter a valid email address";
                hint.style.color = "#6c757d";
            }
            return;
        }

        const suggestion = suggestDomainFix(val);
        if (suggestion) {
            if (hint) {
                hint.innerText = `Did you mean: ${val.split("@")[0]}@${suggestion}?`;
                hint.style.color = "#6c757d";
            }
            input.classList.remove("is-valid", "is-invalid");
            return;
        }

        if (!regex.test(val)) {
            input.classList.add("is-invalid");
            input.classList.remove("is-valid");
            if (hint) {
                hint.innerText = "Invalid email format";
                hint.style.color = "#d9534f";
            }
        } else {
            input.classList.add("is-valid");
            input.classList.remove("is-invalid");
            if (hint) {
                hint.innerText = "Looks good";
                hint.style.color = "#198754";
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("addRoomBookingModal");
        const form = document.getElementById("formAddBooking");
        const clearBtn = document.getElementById("clear-add-form");

        function resetAddForm() {
            if (!form) return;

            form.reset();

            // Email UI reset
            const email = document.getElementById("add-guest-email");
            const hint = document.getElementById("add-email-hint");

            if (email) email.classList.remove("is-valid", "is-invalid");
            if (hint) {
                hint.innerText = "Enter a valid email address";
                hint.style.color = "#6c757d";
            }

            // Reset date fields
            const s = document.getElementById("add-start-date");
            const e = document.getElementById("add-end-date");
            if (s) s.value = "";
            if (e) e.value = "";

            // Reset calendar instance
            if (window.addCal && typeof window.addCal.open === "function") {
                window.addCal.open();
            }
        }

        modal?.addEventListener("show.bs.modal", resetAddForm);
        clearBtn?.addEventListener("click", resetAddForm);

        // Live email validation
        const emailInput = document.getElementById("add-guest-email");
        emailInput?.addEventListener("input", () => {
            validateEmailInput(emailInput, "add-email-hint");
        });
    });

})();
