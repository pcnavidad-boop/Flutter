(function () {
    const Common = window.RoomsCommon;

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalEditRoom");
        const form = document.getElementById("formEditRoom");

        const btnRevert = document.getElementById("btnEditRevert");

        const fields = {
            name: document.getElementById("edit-name"),
            number: document.getElementById("edit-room-number"),
            type: document.getElementById("edit-room-type"),
            status: document.getElementById("edit-status"),
            beds: document.getElementById("edit-number-of-beds"),
            capacity: document.getElementById("edit-capacity"),
            base: document.getElementById("edit-base-price"),
            description: document.getElementById("edit-description"),

            // Price type fields
            priceDisplay: document.getElementById("edit-price-type-display"),
            priceRaw: document.getElementById("edit-price-type-raw")
        };

        let original = {};

        // Modal open: load room data
        modal?.addEventListener("show.bs.modal", event => {

            const id = event.relatedTarget.getAttribute("data-id");
            form.action = `/admin/rooms/${id}`;

            fetch(`/admin/rooms/${id}`)
                .then(res => res.json())
                .then(room => {

                    fields.name.value = room.name;
                    fields.number.value = room.room_number;
                    fields.type.value = room.room_type;

                    fields.status.value = room.status;
                    fields.capacity.value = room.capacity;
                    fields.beds.value = room.beds ?? 0;

                    fields.base.value = room.base_price;
                    fields.description.value = room.description ?? "";

                    // Raw value for backend
                    fields.priceRaw.value = room.price_type;

                    // Human readable
                    fields.priceDisplay.value = Common.toDisplayPriceType(room.price_type);

                    Common.updateHints("edit", room.room_type);

                    // Store originals for revert
                    original = {
                        name: room.name,
                        number: room.room_number,
                        type: room.room_type,
                        status: room.status,
                        beds: room.beds ?? 0,
                        capacity: room.capacity,
                        base: room.base_price,
                        description: room.description ?? "",
                        price_type_raw: room.price_type,
                        price_type_display: Common.toDisplayPriceType(room.price_type)
                    };
                });
        });

        // Type changed → update price type display + raw value
        fields.type?.addEventListener("change", () => {
            const t = fields.type.value;

            // FIXED COMMENT
            const raw = Common.getPriceTypeRaw(t); // returns per_night or per_day
            const display = Common.toDisplayPriceType(raw);

            fields.priceDisplay.value = display;
            fields.priceRaw.value = raw;

            Common.updateHints("edit", t);
        });

        // Revert button
        btnRevert?.addEventListener("click", () => {

            fields.name.value = original.name;
            fields.number.value = original.number;
            fields.type.value = original.type;
            fields.status.value = original.status;
            fields.beds.value = original.beds;
            fields.capacity.value = original.capacity;
            fields.base.value = original.base;
            fields.description.value = original.description;

            fields.priceDisplay.value = original.price_type_display;
            fields.priceRaw.value = original.price_type_raw;

            Common.updateHints("edit", original.type);
        });

        // When modal closes → clean form
        modal?.addEventListener("hidden.bs.modal", () => {
            form.reset();
            original = {};
        });

    });
})();
