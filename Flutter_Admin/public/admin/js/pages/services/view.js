(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalViewService");

        modal?.addEventListener("show.bs.modal", event => {

            const id = event.relatedTarget.getAttribute("data-id");

            fetch(`/admin/services/${id}`)
                .then(r => r.json())
                .then(svc => {

                    document.getElementById("viewServiceImage").src = svc.image_url ?? '';

                    document.getElementById("viewServiceName").innerText = svc.name;
                    document.getElementById("viewServiceType").innerText =
                        svc.service_type.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
                    document.getElementById("viewServiceLocation").innerText =
                        svc.location ?? "—";

                    document.getElementById("viewServicePrice").innerText =
                        svc.formatted_base_price ?? "0.00";

                    // Always show "per person"
                    document.getElementById("viewServicePriceType").innerText =
                        "per person";

                    document.getElementById("viewServiceCapacity").innerText =
                        svc.capacity ?? "—";

                    // Show exact saved hours
                    document.getElementById("viewServiceHours").innerText =
                        `${svc.start_time ?? ''} - ${svc.end_time ?? ''}`;

                    document.getElementById("viewServiceDescription").innerText =
                        svc.description ?? "";
                });
        });

    });

})();
