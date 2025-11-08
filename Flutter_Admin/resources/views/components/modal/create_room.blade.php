<div class="modal fade" id="createRoomModal" tabindex="-1" aria-labelledby="createRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('room.store_data') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createRoomModalLabel">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Room Number</label>
                    <input type="text" name="room_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Room Type</label>
                    <input type="text" name="room_type" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Price per Night</label>
                    <input type="number" name="price_per_night" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Number of beds</label>
                    <input type="number" name="number_of_beds" step="1" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label>Room Capacity</label>
                    <input type="number" name="room_capacity" step="1" class="form-control" required>
                </div>                
                
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="room_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>