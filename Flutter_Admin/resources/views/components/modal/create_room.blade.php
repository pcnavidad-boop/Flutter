<div class="modal fade" id="createRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('room.store_data') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Room</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label>Room Number</label>
                    <input type="text" name="room_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Room Type</label>
                    <select name="type" class="form-select" required>
                        @foreach(['Single','Double','Quad','Family','Suite','Penthouse','Function'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Price Type</label>
                    <select name="price_type" class="form-select" required>
                        <option value="per_night">Per Night</option>
                        <option value="per_hour">Per Hour</option>
                        <option value="per_event">Per Event</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Base Price</label>
                    <input type="number" name="base_price" class="form-control" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label>Number of Beds</label>
                    <input type="number" name="number_of_beds" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['Available','Occupied','Maintenance','Unavailable'] as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label>Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.png,.jpeg,.webp">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>
