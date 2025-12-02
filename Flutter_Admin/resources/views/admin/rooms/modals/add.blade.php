<!-- ADD ROOM MODAL -->
<div class="modal fade @if ($errors->hasBag('addRoom')) show @endif"
     id="modalAddRoom"
     tabindex="-1"
     @if ($errors->hasBag('addRoom')) style="display:block;" @endif>

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="formAddRoom"
                  method="POST"
                  enctype="multipart/form-data"
                  action="{{ route('admin.rooms.store') }}">
                @csrf

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- NAME -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="form-control @error('name','addRoom') is-invalid @enderror">
                            @error('name','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROOM NUMBER -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Number</label>
                            <input type="text"
                                   name="room_number"
                                   value="{{ old('room_number') }}"
                                   class="form-control @error('room_number','addRoom') is-invalid @enderror">
                            @error('room_number','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROOM TYPE -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Room Type</label>
                            <select name="room_type"
                                    id="add-room-type"
                                    class="form-select @error('room_type','addRoom') is-invalid @enderror">
                                <option value="single" {{ old('room_type')=='single'?'selected':'' }}>Single</option>
                                <option value="double" {{ old('room_type')=='double'?'selected':'' }}>Double</option>
                                <option value="quad"   {{ old('room_type')=='quad'?'selected':'' }}>Quad</option>
                                <option value="family" {{ old('room_type')=='family'?'selected':'' }}>Family</option>
                                <option value="suite"  {{ old('room_type')=='suite'?'selected':'' }}>Suite</option>
                                <option value="penthouse" {{ old('room_type')=='penthouse'?'selected':'' }}>Penthouse</option>
                                <option value="function" {{ old('room_type')=='function'?'selected':'' }}>Function Room</option>
                            </select>
                            @error('room_type','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- BEDS -->
                        <div class="col-md-4" id="add-beds-block">
                            <label class="form-label fw-semibold">Number of Beds</label>
                            <input type="number"
                                   name="number_of_beds"
                                   id="add-number-of-beds"
                                   value="{{ old('number_of_beds', 1) }}"
                                   min="0"
                                   class="form-control @error('number_of_beds','addRoom') is-invalid @enderror">
                            <small id="add-beds-hint" class="text-muted small"></small>
                            @error('number_of_beds','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CAPACITY -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Capacity</label>
                            <input type="number"
                                   name="capacity"
                                   id="add-capacity"
                                   value="{{ old('capacity') }}"
                                   class="form-control @error('capacity','addRoom') is-invalid @enderror">
                            <small id="add-capacity-hint" class="text-muted small"></small>
                            @error('capacity','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- PRICE + PRICE TYPE -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Base Price</label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       name="base_price"
                                       value="{{ old('base_price') }}"
                                       class="form-control @error('base_price','addRoom') is-invalid @enderror">
                                <input type="text"
                                       name="price_type"
                                       id="add-price-type"
                                       value="per night"
                                       readonly
                                       class="form-control"
                                       style="max-width:150px;">
                            </div>
                            @error('base_price','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- IMAGE -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Room Image</label>
                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="form-control @error('image','addRoom') is-invalid @enderror">
                            @error('image','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description"
                                      rows="3"
                                      class="form-control @error('description','addRoom') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer justify-content-end">
                    <button class="btn btn-coffee">Save Room</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Reset modal on close and enforce function-room beds logic --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Hide or show beds and set default correctly for function rooms (add)
    const addType = document.getElementById('add-room-type');
    const addBedsBlock = document.getElementById('add-beds-block');
    const addBedsInput = document.getElementById('add-number-of-beds');
    const addBedsHint = document.getElementById('add-beds-hint');
    const addPriceType = document.getElementById('add-price-type');

    function setAddBedsForType(type){
        if(type === 'function'){
            if(addBedsBlock) addBedsBlock.style.display = 'none';
            if(addBedsInput) addBedsInput.value = 0;
            if(addBedsHint) addBedsHint.innerText = '';
        } else {
            if(addBedsBlock) addBedsBlock.style.display = 'block';
            if(addBedsInput && !addBedsInput.value) addBedsInput.value = 1;
        }

        if(addPriceType){
            addPriceType.value = (type === 'function' ? 'per day' : 'per night');
        }
    }

    if(addType){
        addType.addEventListener('change', e => setAddBedsForType(e.target.value));
        setAddBedsForType(addType.value);
    }

    // When modal closes, reset form + validation states
    const addModalEl = document.getElementById('modalAddRoom');
    if(addModalEl){
        addModalEl.addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('formAddRoom');
            if(form) form.reset();

            // re-initialize defaults after reset
            setTimeout(()=> {
                setAddBedsForType(addType ? addType.value : '');
                // clear validation classes
                form && form.querySelectorAll('.is-invalid, .is-valid').forEach(n => n.classList.remove('is-invalid','is-valid'));
            }, 50);
        });
    }

});
</script>
@endpush
