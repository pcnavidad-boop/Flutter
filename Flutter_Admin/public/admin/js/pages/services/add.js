(function () {

    const Common = window.ServicesCommon;

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalAddService");
        const form  = document.getElementById("formAddService");

        const typeInput = document.getElementById("add-service-type");
        const priceType = document.getElementById("add-price-type");

        function applyTypeSettings() {
            const type = typeInput?.value ?? "";
            Common.updateHints("add", type);

            // price type display (always per person)
            if (priceType) {
                priceType.value = Common.getPriceType(type);
            }
        }

        typeInput?.addEventListener("change", applyTypeSettings);
        applyTypeSettings();

        modal?.addEventListener("hidden.bs.modal", () => {
            form?.reset();
            // small timeout to allow browser to reset time inputs
            setTimeout(applyTypeSettings, 50);
        });

    });

})();
