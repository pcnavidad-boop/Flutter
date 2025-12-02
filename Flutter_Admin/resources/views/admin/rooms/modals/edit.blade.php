<!-- EDIT ROOM MODAL -->
<div class="modal fade @if ($errors->hasBag('editRoom')) show @endif"
     id="modalEditRoom"
     tabindex="-1"
     @if ($errors->hasBag('editRoom')) style="display:block;" @endif>

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="formEditRoom" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- NAME -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Name</label>
                            <input type="text"
                                   id="edit-name"
                                   name="name"
                                   class="form-control @error('name','editRoom') is-invalid @enderror">
                            @error('name','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROOM NUMBER -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Number</label>
                            <input type="text"
                                   id="edit-room-number"
                                   name="room_number"
                                   class="form-control @error('room_number','editRoom') is-invalid @enderror">
                            @error('room_number','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROOM TYPE -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Room Type</label>
                            <select id="edit-room-type"
                                    name="room_type"
                                    class="form-select @error('room_type','editRoom') is-invalid @enderror">
                                <option value="single">Single</option>
                                <option value="double">Double</option>
                                <option value="quad">Quad</option>
                                <option value="family">Family</option>
                                <option value="suite">Suite</option>
                                <option value="penthouse">Penthouse</option>
                                <option value="function">Function Room</option>
                            </select>
                            @error('room_type','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- BEDS -->
                        <div class="col-md-4" id="edit-beds-block">
                            <label class="form-label fw-semibold">Beds</label>
                            <input type="number"
                                   id="edit-number-of-beds"
                                   name="number_of_beds"
                                   min="0"
                                   value="1"
                                   class="form-control @error('number_of_beds','editRoom') is-invalid @enderror">
                            <small id="edit-beds-hint" class="text-muted small"></small>
                            @error('number_of_beds','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CAPACITY -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Capacity</label>
                            <input type="number"
                                   id="edit-capacity"
                                   name="capacity"
                                   class="form-control @error('capacity','editRoom') is-invalid @enderror">
                            <small id="edit-capacity-hint" class="text-muted small"></small>
                            @error('capacity','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- PRICE + PRICE TYPE -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Base Price</label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       id="edit-base-price"
                                       name="base_price"
                                       class="form-control @error('base_price','editRoom') is-invalid @enderror">

                                <input type="text"
                                       id="edit-price-type"
                                       name="price_type"
                                       readonly
                                       class="form-control"
                                       style="max-width:150px;">
                            </div>
                            @error('base_price','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- IMAGE -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Replace Image</label>
                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="form-control @error('image','editRoom') is-invalid @enderror">
                            @error('image','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- STATUS -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select id="edit-status"
                                    name="status"
                                    class="form-select @error('status','editRoom') is-invalid @enderror">
                                <option value="available">Available</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            @error('status','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea id="edit-description"
                                      name="description"
                                      class="form-control @error('description','editRoom') is-invalid @enderror"
                                      rows="3"></textarea>
                            @error('description','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer justify-content-end">
                    <button class="btn btn-coffee">Save Changes</button>
                </div>

            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // When edit modal opens we populate values (index script already sets form.action & fields)
    // Ensure function room behavior enforced when type changes (index script already attaches listener)
    const editType = document.getElementById('edit-room-type');
    const editBedsBlock = document.getElementById('edit-beds-block');
    const editBedsInput = document.getElementById('edit-number-of-beds');
    const editBedsHint = document.getElementById('edit-beds-hint');
    const editPriceType = document.getElementById('edit-price-type');

    function setEditBedsForType(type){
        if(type === 'function'){
            if(editBedsBlock) editBedsBlock.style.display = 'none';
            if(editBedsInput) editBedsInput.value = 0;
            if(editBedsHint) editBedsHint.innerText = '';
        } else {
            if(editBedsBlock) editBedsBlock.style.display = 'block';
            if(editBedsInput && !editBedsInput.value) editBedsInput.value = 1;
        }

        if(editPriceType){
            editPriceType.value = (type === 'function' ? 'per day' : 'per night');
        }
    }

    if(editType){
        editType.addEventListener('change', e => setEditBedsForType(e.target.value));
        // initial will be set by index modal show handler after fetch
    }

    // auto-open edit modal if server-side validation error exists (index already handles this)
    @if ($errors->hasBag('editRoom'))
        new bootstrap.Modal(document.getElementById('modalEditRoom')).show();
    @endif

});
</script>
@endpush
