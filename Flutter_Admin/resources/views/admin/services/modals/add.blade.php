<!-- ADD SERVICE MODAL -->
<div class="modal fade" id="modalAddService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <form id="formAddService" method="POST" enctype="multipart/form-data"
                  action="{{ route('admin.services.store') }}">
                @csrf

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">Add Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    @if ($errors->addService->any())
                        <div class="alert alert-danger">Please correct the errors below.</div>
                    @endif

                    {{-- BASIC INFORMATION --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Basic Information</h6>

                            <div class="row g-3">

                                {{-- NAME --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Service Name</label>
                                    <input type="text"
                                           name="name"
                                           class="form-control @error('name','addService') is-invalid @enderror"
                                           value="{{ old('name') }}">
                                    @error('name','addService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- LOCATION --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Location</label>
                                    <input type="text"
                                           name="location"
                                           class="form-control @error('location','addService') is-invalid @enderror"
                                           value="{{ old('location') }}">
                                    @error('location','addService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- TYPE --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Service Type</label>
                                    <select id="add-service-type"
                                            name="service_type"
                                            class="form-select @error('service_type','addService') is-invalid @enderror">
                                        @foreach(['restaurant','spa','gym','swimming_pool','bar'] as $type)
                                            <option value="{{ $type }}"
                                                @selected(old('service_type') === $type)>
                                                {{ ucfirst(str_replace('_',' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_type','addService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- SCHEDULE --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Schedule</h6>

                            <div class="row g-3">
                                {{-- START TIME --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Start Time</label>
                                    <input type="time"
                                           id="add-start-time"
                                           name="start_time"
                                           step="3600"
                                           class="form-control @error('start_time','addService') is-invalid @enderror"
                                           value="{{ old('start_time') }}">
                                    @error('start_time','addService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="add-time-hint" class="text-muted d-block mt-1">Hourly slots preferred (e.g. 09:00). Minute granularity allowed for gym/pool.</small>
                                </div>

                                {{-- END TIME --}}
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">End Time</label>
                                    <input type="time"
                                           id="add-end-time"
                                           name="end_time"
                                           step="3600"
                                           class="form-control @error('end_time','addService') is-invalid @enderror"
                                           value="{{ old('end_time') }}">
                                    @error('end_time','addService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- CAPACITY --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Capacity</h6>

                            <input type="number"
                                   id="add-capacity"
                                   name="capacity"
                                   class="form-control @error('capacity','addService') is-invalid @enderror"
                                   value="{{ old('capacity') }}">
                            <small id="add-capacity-hint" class="text-muted small"></small>

                            @error('capacity','addService')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- PRICING --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Pricing</h6>

                            <div class="row g-3">

                                {{-- BASE PRICE --}}
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Base Price</label>
                                    <input type="number"
                                           step="50.00"
                                           name="base_price"
                                           class="form-control @error('base_price','addService') is-invalid @enderror"
                                           value="{{ old('base_price') }}">
                                    @error('base_price','addService')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- PRICE TYPE: visible readonly + hidden actual value --}}
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Price Type</label>
                                    <input id="add-price-type" type="text" class="form-control" readonly value="per person">
                                    <input type="hidden" name="price_type" value="per_person">
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
                                   class="form-control @error('image','addService') is-invalid @enderror">
                            @error('image','addService')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">Description</h6>

                            <textarea name="description"
                                      rows="3"
                                      class="form-control @error('description','addService') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description','addService')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 justify-content-end">
                    <button class="btn btn-coffee rounded-pill px-4">Save Service</button>
                </div>

            </form>

        </div>
    </div>
</div>
