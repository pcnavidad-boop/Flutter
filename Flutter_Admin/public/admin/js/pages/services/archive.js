(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalArchiveService");

        modal?.addEventListener("show.bs.modal", event => {

            const id = event.relatedTarget.getAttribute("data-id");
            const form = document.getElementById("formArchiveService");
            const select = document.getElementById("archiveSelectService");

            form.action = `/admin/services/${id}/archive`;

            fetch(`/admin/services/${id}`)
                .then(r => r.json())
                .then(svc => {
                    select.value = svc.is_archived ? "0" : "1";
                });
        });

    });

})();
