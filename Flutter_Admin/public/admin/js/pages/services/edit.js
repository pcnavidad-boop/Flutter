(function () {

    const Common = window.ServicesCommon;

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalEditService");
        const form  = document.getElementById("formEditService");

        const typeInput = document.getElementById("edit-service-type");
        const priceTypeInput = document.getElementById("edit-price-type");

        modal?.addEventListener("show.bs.modal", event => {

            const id = event.relatedTarget.getAttribute("data-id");
            if (!id) return;

            form.action = `/admin/services/${id}`;

            fetch(`/admin/services/${id}`)
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(svc => {

                    document.getElementById("edit-name").value = svc.name;
                    document.getElementById("edit-location").value = svc.location ?? "";
                    document.getElementById("edit-service-type").value = svc.service_type;

                    document.getElementById("edit-capacity").value = svc.capacity ?? "";
                    document.getElementById("edit-base-price").value = svc.base_price;

                    // ensure time inputs exist with these IDs
                    const startField = document.getElementById("edit-start-time");
                    const endField = document.getElementById("edit-end-time");

                    if (startField) startField.value = svc.start_time ?? "";
                    if (endField) endField.value = svc.end_time ?? "";

                    document.getElementById("edit-status").value = svc.status;
                    document.getElementById("edit-description").value = svc.description ?? "";

                    // Always show "per person" (UI only)
                    priceTypeInput.value = Common.getPriceType(svc.service_type);

                    // Update hints and time step based on type
                    Common.updateHints("edit", svc.service_type);
                }).catch(() => {
                    // fail quietly; modal will still open but without populated values
                    console.error("Failed to fetch service data for edit modal.");
                });
        });

        typeInput?.addEventListener("change", () => {
            const type = typeInput.value;
            priceTypeInput.value = Common.getPriceType(type);
            Common.updateHints("edit", type);
        });

    });

})();
