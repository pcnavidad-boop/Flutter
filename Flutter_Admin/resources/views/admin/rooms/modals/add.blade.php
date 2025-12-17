<!-- ADD ROOM MODAL -->
<div class="modal fade" id="modalAddRoom" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <form id="formAddRoom"
                  method="POST"
                  enctype="multipart/form-data"
                  action="{{ route('admin.rooms.store') }}">
                @csrf

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">Add Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- show summary if errors exist in addRoom bag --}}
                    @if ($errors->addRoom->any())
                        <div class="alert alert-danger">
                            Please fix the highlighted errors below.
                        </div>
                    @endif

                    {{-- ROOM INFORMATION --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">
                                Room Information
                            </h6>

                            <div class="row g-3">

                                {{-- NAME --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Room Name</label>
                                    <input type="text"
                                           name="name"
                                           class="form-control @error('name','addRoom') is-invalid @enderror"
                                           value="{{ old('name') }}" required>
                                    @error('name','addRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NUMBER --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Room Number</label>
                                    <input type="text"
                                           name="room_number"
                                           class="form-control @error('room_number','addRoom') is-invalid @enderror"
                                           value="{{ old('room_number') }}" required>
                                    @error('room_number','addRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- TYPE --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Room Type</label>
                                    <select name="room_type"
                                            id="add-room-type"
                                            class="form-select @error('room_type','addRoom') is-invalid @enderror">
                                        @foreach(['single','double','quad','family','suite','penthouse','function'] as $type)
                                            <option value="{{ $type }}"
                                                @selected(old('room_type') === $type)>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_type','addRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CAPACITY & BEDS --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Capacity & Beds</h6>

                            <div class="row g-3">

                                {{-- BEDS --}}
                                <div class="col-md-6" id="add-beds-block">
                                    <label class="form-label text-muted small">Beds</label>
                                    <input type="number"
                                           name="number_of_beds"
                                           id="add-number-of-beds"
                                           min="0"
                                           value="{{ old('number_of_beds', 1) }}"
                                           class="form-control @error('number_of_beds','addRoom') is-invalid @enderror">
                                    <small id="add-beds-hint" class="text-muted small"></small>

                                    @error('number_of_beds','addRoom')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- CAPACITY --}}
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Capacity</label>
                                    <input type="number"
                                           name="capacity"
                                           id="add-capacity"
                                           class="form-control @error('capacity','addRoom') is-invalid @enderror"
                                           value="{{ old('capacity') }}" required>
                                    <small id="add-capacity-hint" class="text-muted small"></small>

                                    @error('capacity','addRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- PRICING --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Pricing</h6>

                            <div class="row g-3">

                                {{-- BASE PRICE --}}
                                <div class="col-md-8">
                                    <label class="form-label text-muted small">Base Price</label>
                                    <input type="number"
                                        step="50.00"
                                        name="base_price"
                                        class="form-control @error('base_price','addRoom') is-invalid @enderror"
                                        value="{{ old('base_price') }}" required>

                                    @error('base_price','addRoom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- PRICE TYPE --}}
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Price Type</label>

                                    <!-- Human-readable label -->
                                    <input type="text"
                                        id="add-price-type-display"
                                        class="form-control"
                                        readonly
                                        value="{{ old('price_type') === 'per_day' ? 'per day' : 'per night' }}">

                                    <!-- Raw backend value -->
                                    <input type="hidden"
                                        id="add-price-type-raw"
                                        name="price_type"
                                        value="{{ old('price_type') }}">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- IMAGE --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Image Upload</h6>

                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="form-control @error('image','addRoom') is-invalid @enderror">

                            @error('image','addRoom')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Description</h6>

                            <textarea name="description" rows="3"
                                      class="form-control @error('description','addRoom') is-invalid @enderror">{{ old('description') }}</textarea>

                            @error('description','addRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 justify-content-between">
                    <button type="button" id="btnAddClear" class="btn btn-light rounded-pill px-4">
                        Clear Form
                    </button>

                    <button type="submit" class="btn btn-coffee rounded-pill px-4">
                        Save Room
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
