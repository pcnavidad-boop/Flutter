(function () {

    document.addEventListener("DOMContentLoaded", () => {

        const modal = document.getElementById("modalViewRoom");

        modal?.addEventListener("show.bs.modal", event => {
            const id = event.relatedTarget.getAttribute("data-id");

            fetch(`/admin/rooms/${id}`)
                .then(r => r.json())
                .then(room => {

                    document.getElementById("viewRoomImage").src = room.image_url ?? '/admin/images/placeholder-room.png';

                    document.getElementById("viewRoomName").innerText = room.name;
                    document.getElementById("viewRoomNumber").innerText = room.room_number;
                    document.getElementById("viewRoomType").innerText = room.room_type;

                    // Use formatted values (₱1,000.00 and per night / per day)
                    document.getElementById("viewRoomPrice").innerText = room.formatted_base_price;
                    document.getElementById("viewRoomPriceType").innerText = room.formatted_price_type;

                    document.getElementById("viewRoomCapacity").innerText = room.capacity ?? "";
                    document.getElementById("viewRoomBeds").innerText = room.beds ?? "";
                    document.getElementById("viewRoomDescription").innerText = room.description ?? "";
                });
        });

    });

})();
