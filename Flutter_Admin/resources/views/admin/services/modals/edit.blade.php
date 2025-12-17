<!-- EDIT SERVICE MODAL -->
<div class="modal fade" id="modalEditService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <form id="formEditService" method="POST" enctype="multipart/form-data">
                @csrf
                @method("PUT")

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    @if ($errors->editService->any())
                        <div class="alert alert-danger">Please correct the errors below.</div>
                    @endif

                    {{-- BASIC INFO --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">

                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">
                                Service Information
                            </h6>

                            <div class="row g-3">

                                {{-- NAME --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Service Name</label>
                                    <input id="edit-name"
                                           name="name"
                                           type="text"
                                           class="form-control @error('name','editService') is-invalid @enderror"
                                           value="{{ old('name') }}">
                                    @error('name','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- LOCATION --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Location</label>
                                    <input id="edit-location"
                                           name="location"
                                           type="text"
                                           class="form-control @error('location','editService') is-invalid @enderror"
                                           value="{{ old('location') }}">
                                    @error('location','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- TYPE --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Service Type</label>
                                    <select id="edit-service-type"
                                            name="service_type"
                                            class="form-select @error('service_type','editService') is-invalid @enderror">
                                        @foreach(['restaurant','spa','gym','swimming_pool','bar'] as $type)
                                            <option value="{{ $type }}"
                                                @selected(old('service_type') === $type)>
                                                {{ ucfirst(str_replace('_',' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_type','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- STATUS --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Status</label>
                                    <select id="edit-status"
                                            name="status"
                                            class="form-select @error('status','editService') is-invalid @enderror">
                                        <option value="available" @selected(old('status')==='available')>Available</option>
                                        <option value="maintenance" @selected(old('status')==='maintenance')>Maintenance</option>
                                    </select>
                                    @error('status','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- SCHEDULE --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">Schedule</h6>

                            <div class="row g-3">

                                {{-- START --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Start Time</label>
                                    <input id="edit-start-time"
                                           name="start_time"
                                           type="time"
                                           step="3600"
                                           class="form-control @error('start_time','editService') is-invalid @enderror"
                                           value="{{ old('start_time') }}">
                                    @error('start_time','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="edit-time-hint" class="text-muted d-block mt-1">Hourly slots preferred (e.g. 09:00). Minute granularity allowed for gym/pool.</small>
                                </div>

                                {{-- END --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">End Time</label>
                                    <input id="edit-end-time"
                                           name="end_time"
                                           type="time"
                                           step="3600"
                                           class="form-control @error('end_time','editService') is-invalid @enderror"
                                           value="{{ old('end_time') }}">
                                    @error('end_time','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- CAPACITY --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">Capacity</h6>

                            <input id="edit-capacity"
                                   name="capacity"
                                   type="number"
                                   class="form-control @error('capacity','editService') is-invalid @enderror"
                                   value="{{ old('capacity') }}">
                            <small id="edit-capacity-hint" class="text-muted small"></small>

                            @error('capacity','editService')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- PRICING --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">Pricing</h6>

                            <div class="row g-3">

                                {{-- BASE PRICE --}}
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Base Price</label>
                                    <input type="number"
                                           step="50.00"
                                           id="edit-base-price"
                                           name="base_price"
                                           class="form-control @error('base_price','editService') is-invalid @enderror"
                                           value="{{ old('base_price') }}">
                                    @error('base_price','editService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- PRICE TYPE (READONLY + hidden) --}}
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Price Type</label>
                                    <input id="edit-price-type" type="text" class="form-control" readonly value="per person">
                                    <input type="hidden" name="price_type" value="per_person">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- IMAGE --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">Replace Image</h6>

                            <input name="image"
                                   type="file"
                                   class="form-control @error('image','editService') is-invalid @enderror">
                            @error('image','editService')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="fw-bold text-uppercase small mb-3" style="color:#6a4e32;">Description</h6>

                            <textarea id="edit-description"
                                      name="description"
                                      rows="3"
                                      class="form-control @error('description','editService') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description','editService')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 justify-content-end">
                    <button class="btn btn-coffee rounded-pill px-4">Save Changes</button>
                </div>

            </form>

        </div>
    </div>
</div>
