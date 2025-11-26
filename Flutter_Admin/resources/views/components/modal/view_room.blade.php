<div class="modal fade" id="viewRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Room Details</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <h4 class="fw-bold mb-3">
                    Room <span id="view_room_number"></span>
                </h4>

                <p><strong>Type:</strong> <span id="view_type"></span></p>

                <p><strong>Price Type:</strong> <span id="view_price_type"></span></p>
                <p><strong>Base Price:</strong> ₱<span id="view_base_price"></span></p>

                <p><strong>Is Time Based:</strong> <span id="view_is_time_based"></span></p>

                <hr>

                <p><strong>Beds:</strong> <span id="view_number_of_beds"></span></p>
                <p><strong>Capacity:</strong> <span id="view_capacity"></span> guests</p>

                <p><strong>Status:</strong> <span id="view_status" class="badge bg-info text-dark"></span></p>

                <hr>

                <p><strong>Description:</strong></p>
                <p id="view_description" class="text-muted"></p>

            </div>

        </div>
    </div>
</div>
