<!-- ADD SERVICE MODAL -->
<div class="modal fade" id="modalAddService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- ROW 1 -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Name</label>
                            <input name="name" type="text" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <input name="location" type="text" value="{{ old('location') }}"
                                   class="form-control @error('location') is-invalid @enderror">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROW 2 -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Type</label>
                            <select id="add-service-type" name="service_type"
                                    class="form-select @error('service_type') is-invalid @enderror">
                                <option value="restaurant">Restaurant</option>
                                <option value="spa">Spa</option>
                                <option value="gym">Gym</option>
                                <option value="swimming_pool">Swimming Pool</option>
                                <option value="bar">Bar</option>
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capacity</label>
                            <input name="capacity" type="number" value="{{ old('capacity') }}"
                                   class="form-control @error('capacity') is-invalid @enderror">
                            <small id="add-capacity-hint" class="text-muted small"></small>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROW 3 -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Base Price</label>
                            <div class="input-group">
                                <input name="base_price" type="number" step="0.01" value="{{ old('base_price') }}"
                                       class="form-control @error('base_price') is-invalid @enderror">
                                <input id="add-price-type" name="price_type"
                                       type="text" readonly class="form-control" style="max-width:150px;">
                            </div>
                            @error('base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROW 4 -->
                        <div class="col-md-6 d-flex gap-3">

                            <div class="flex-fill">
                                <label class="form-label fw-semibold">Start Time</label>
                                <input name="start_time" type="time" value="{{ old('start_time') }}"
                                       class="form-control @error('start_time') is-invalid @enderror">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="flex-fill">
                                <label class="form-label fw-semibold">End Time</label>
                                <input name="end_time" type="time" value="{{ old('end_time') }}"
                                       class="form-control @error('end_time') is-invalid @enderror">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <!-- ROW 5 -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Image</label>
                            <input name="image" type="file" accept="image/*"
                                   class="form-control @error('image') is-invalid @enderror">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ROW 7 -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer justify-content-end">
                    <button class="btn btn-coffee">Save Service</button>
                </div>

            </form>

        </div>
    </div>
</div>
