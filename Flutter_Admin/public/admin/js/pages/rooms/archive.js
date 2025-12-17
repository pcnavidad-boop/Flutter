(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalArchiveRoom");

        modal?.addEventListener("show.bs.modal", event => {

            const id = event.relatedTarget.getAttribute("data-id");
            const form = document.getElementById("formArchiveRoom");
            const select = document.getElementById("archiveSelect");

            form.action = `/admin/rooms/${id}/archive`;

            fetch(`/admin/rooms/${id}`)
                .then(r => r.json())
                .then(room => {
                    select.value = room.is_archived ? "0" : "1";
                });
        });

    });

})();
