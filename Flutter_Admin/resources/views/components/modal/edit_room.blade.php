<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editRoomForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="editModalAlerts"></div>

                <div class="mb-3">
                    <label>Room Number</label>
                    <input type="text" id="edit_room_number" name="room_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Room Type</label>
                    <input type="text" id="edit_room_type" name="room_type" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Price per Night</label>
                    <input type="number" name="price_per_night" id="edit_price_per_night" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Number of beds</label>
                    <input type="number" name="number_of_beds" id="edit_number_of_beds" step="1" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Room Capacity</label>
                    <input type="number" name="room_capacity" id="edit_room_capacity" step="1" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Availability</label>
                    <select name="room_availability_status" id="edit_room_availability" class="form-select">
                        <option value="1">Available</option>
                        <option value="0">Unavailable</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="room_description" id="edit_room_description" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Update</button>
            </div>



        </form>
    </div>
</div>