<!-- EDIT ROOM MODAL -->
<div class="modal fade" id="modalEditRoom" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <form id="formEditRoom" method="POST" enctype="multipart/form-data">
                @csrf
                @method("PUT")

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- GLOBAL ERROR SUMMARY --}}
                    @if ($errors->editRoom->any())
                        <div class="alert alert-danger">
                            Please fix the errors below.
                        </div>
                    @endif

                    {{-- ROOM INFORMATION --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">
                                Room Information
                            </h6>

                            <div class="row g-3">

                                {{-- NAME --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Room Name</label>
                                    <input type="text"
                                           id="edit-name"
                                           name="name"
                                           class="form-control @error('name','editRoom') is-invalid @enderror"
                                           value="{{ old('name') }}">
                                    @error('name','editRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ROOM NUMBER --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Room Number</label>
                                    <input type="text"
                                           id="edit-room-number"
                                           name="room_number"
                                           class="form-control @error('room_number','editRoom') is-invalid @enderror"
                                           value="{{ old('room_number') }}">
                                    @error('room_number','editRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ROOM TYPE --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Room Type</label>
                                    <select id="edit-room-type"
                                            name="room_type"
                                            class="form-select @error('room_type','editRoom') is-invalid @enderror">
                                        @foreach(['single','double','quad','family','suite','penthouse','function'] as $type)
                                            <option value="{{ $type }}"
                                                @selected(old('room_type') === $type)>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_type','editRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- STATUS --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Status</label>
                                    <select id="edit-status"
                                            name="status"
                                            class="form-select @error('status','editRoom') is-invalid @enderror">
                                        <option value="available" @selected(old('status')==='available')>Available</option>
                                        <option value="maintenance" @selected(old('status')==='maintenance')>Maintenance</option>
                                    </select>
                                    @error('status','editRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CAPACITY & BEDS --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">
                                Capacity & Beds
                            </h6>

                            <div class="row g-3">

                                {{-- BEDS --}}
                                <div class="col-md-6" id="edit-beds-block">
                                    <label class="form-label small text-muted">Beds</label>
                                    <input type="number"
                                           min="0"
                                           id="edit-number-of-beds"
                                           name="number_of_beds"
                                           class="form-control @error('number_of_beds','editRoom') is-invalid @enderror"
                                           value="{{ old('number_of_beds') }}">
                                    <small id="edit-beds-hint" class="text-muted small"></small>

                                    @error('number_of_beds','editRoom')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- CAPACITY --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Capacity</label>
                                    <input type="number"
                                           id="edit-capacity"
                                           name="capacity"
                                           class="form-control @error('capacity','editRoom') is-invalid @enderror"
                                           value="{{ old('capacity') }}">
                                    <small id="edit-capacity-hint" class="text-muted small"></small>

                                    @error('capacity','editRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- PRICING --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">
                                Pricing
                            </h6>

                            <div class="row g-3">

                                {{-- BASE PRICE --}}
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Base Price</label>
                                    <input type="number"
                                           step="50.00"
                                           id="edit-base-price"
                                           name="base_price"
                                           class="form-control @error('base_price','editRoom') is-invalid @enderror"
                                           value="{{ old('base_price') }}">
                                    @error('base_price','editRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- PRICE TYPE (DISPLAY + HIDDEN RAW) --}}
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Price Type</label>

                                    <!-- READABLE LABEL -->
                                    <input type="text"
                                        id="edit-price-type-display"
                                        class="form-control"
                                        readonly
                                        value="{{ old('price_type') === 'per_day' ? 'per day' : 'per night' }}">

                                    <!-- RAW VALUE FOR BACKEND -->
                                    <input type="hidden"
                                        id="edit-price-type-raw"
                                        name="price_type"
                                        value="{{ old('price_type') }}">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- IMAGE --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">
                                Replace Image
                            </h6>

                            <input type="file"
                                   name="image"
                                   class="form-control @error('image','editRoom') is-invalid @enderror"
                                   accept="image/*">

                            @error('image','editRoom')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">
                                Description
                            </h6>

                            <textarea id="edit-description"
                                      name="description"
                                      rows="3"
                                      class="form-control @error('description','editRoom') is-invalid @enderror">{{ old('description') }}</textarea>

                            @error('description','editRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer border-0 justify-content-between">

                    <button type="button"
                            id="btnEditRevert"
                            class="btn btn-light rounded-pill px-4">
                        Revert Changes
                    </button>

                    <button type="submit"
                            class="btn btn-coffee rounded-pill px-4">
                        Save Changes
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
