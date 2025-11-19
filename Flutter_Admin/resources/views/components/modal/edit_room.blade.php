<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editRoomForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Room</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label>Room Number</label>
                    <input type="text" id="edit_room_number" name="room_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Room Type</label>
                    <select id="edit_type" name="type" class="form-select" required>
                        @foreach(['Single','Double','Quad','Family','Suite','Penthouse','Function'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Price Type</label>
                    <select id="edit_price_type" name="price_type" class="form-select" required>
                        <option value="per_night">Per Night</option>
                        <option value="per_hour">Per Hour</option>
                        <option value="per_event">Per Event</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Base Price</label>
                    <input type="number" id="edit_base_price" name="base_price" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Number of Beds</label>
                    <input type="number" id="edit_number_of_beds" name="number_of_beds" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Capacity</label>
                    <input type="number" id="edit_capacity" name="capacity" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select id="edit_status" name="status" class="form-select" required>
                        @foreach(['Available','Occupied','Maintenance','Unavailable'] as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label>Update Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.png,.jpeg,.webp">
                </div>

                <div class="mb-3">
                    <label>Archive Status</label>
                    <select id="edit_is_archived" name="is_archived" class="form-select">
                        <option value="0">Active</option>
                        <option value="1">Archived</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success">Update</button>
            </div>

        </form>
    </div>
</div>
