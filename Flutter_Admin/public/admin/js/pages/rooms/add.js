(function () {

    const Common = window.RoomsCommon;

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalAddRoom");
        const form  = document.getElementById("formAddRoom");

        const typeInput = document.getElementById("add-room-type");
        const priceDisplay = document.getElementById("add-price-type-display");
        const priceRaw = document.getElementById("add-price-type-raw");
        const bedsBlock = document.getElementById("add-beds-block");
        const bedsInput = document.getElementById("add-number-of-beds");

        const btnClear = document.getElementById("btnAddClear");

        function applyTypeSettings() {
            const type = typeInput.value;

            // Update dynamic hints
            Common.updateHints("add", type);

            // Determine correct price type
            const raw = Common.getPriceTypeRaw(type); // per_night or per_day
            priceRaw.value = raw;
            priceDisplay.value = Common.toDisplayPriceType(raw);

            // Beds visibility & defaults
            if (type === "function") {
                bedsBlock.style.display = "none";
                bedsInput.value = 0;
            } else {
                bedsBlock.style.display = "block";
                if (!bedsInput.value || bedsInput.value === "0") {
                    bedsInput.value = 1;
                }
            }
        }

        // Handle room type change
        typeInput?.addEventListener("change", applyTypeSettings);

        // Clear button
        btnClear?.addEventListener("click", () => {
            form.reset();
            setTimeout(applyTypeSettings, 20);
        });

        // When modal opens, enforce correct values
        modal?.addEventListener("show.bs.modal", () => {
            setTimeout(() => {
                applyTypeSettings();
            }, 5);
        });

        // Reset again when modal closes
        modal?.addEventListener("hidden.bs.modal", () => {
            form.reset();
            setTimeout(applyTypeSettings, 20);
        });

        // Initial run
        applyTypeSettings();
    });

})();
