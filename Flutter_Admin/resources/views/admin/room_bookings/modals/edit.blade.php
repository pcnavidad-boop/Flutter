{{-- resources/views/admin/room_bookings/modals/edit.blade.php --}}

<div class="modal fade" id="editRoomBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color:#4a3426;">Edit Room Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="edit-room-booking-form">
                @csrf
                @method('PUT')

                <div class="modal-body">

                    {{-- Display validation errors (this will show if controller returns errors and sets session('edit_id')) --}}
                    @if($errors->any() && session('edit_id'))
                        <div class="alert alert-danger small">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- =======================
                        GUEST INFO
                    ======================== --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">

                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">
                                Guest Information
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Guest Name</label>
                                    <input type="text"
                                           name="guest_name"
                                           id="edit-guest-name"
                                           class="form-control"
                                           value="{{ old('guest_name') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Email</label>
                                    <input type="email"
                                           name="guest_email"
                                           id="edit-guest-email"
                                           class="form-control"
                                           value="{{ old('guest_email') }}"
                                           required>
                                    <small id="edit-email-hint" class="text-muted">Enter a valid email address</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Contact Number</label>
                                    <input type="text"
                                           name="guest_contact"
                                           id="edit-guest-contact"
                                           class="form-control"
                                           maxlength="11"
                                           value="{{ old('guest_contact') }}"
                                           placeholder="09XXXXXXXXX"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Guests</label>
                                    <input type="number"
                                           name="number_of_guests"
                                           id="edit-guests"
                                           min="1"
                                           class="form-control"
                                           value="{{ old('number_of_guests') }}"
                                           required>
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- =======================
                        BOOKING DETAILS
                    ======================== --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">

                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">
                                Booking Details
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Room</label>
                                    <select name="room_id"
                                            id="edit-room-id"
                                            class="form-select"
                                            required>
                                        {{-- Use model directly --}}
                                        @foreach(\App\Models\Room::active()->get() as $room)
                                            <option value="{{ $room->id }}"
                                                @selected(old('room_id') == $room->id)>
                                                {{ $room->name }} — {{ $room->price_label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Booking Type</label>
                                    <select name="type" id="edit-type" class="form-select">
                                        <option value="walk-in" @selected(old('type') == 'walk-in')>Walk-in</option>
                                        <option value="phone" @selected(old('type') == 'phone')>Phone</option>
                                        <option value="email" @selected(old('type') == 'email')>Email</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- =======================
                        SCHEDULE
                    ======================== --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">

                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">
                                Schedule
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Start Date</label>
                                    <input type="date"
                                           id="edit-start-date"
                                           name="start_date"
                                           class="form-control"
                                           readonly
                                           value="{{ old('start_date') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">End Date</label>
                                    <input type="date"
                                           id="edit-end-date"
                                           name="end_date"
                                           class="form-control"
                                           readonly
                                           value="{{ old('end_date') }}"
                                           required>
                                </div>

                                <div class="col-12">
                                    <button type="button" class="btn btn-outline-coffee rounded-pill w-100"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#editCalendarDropdown">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Adjust Dates (Availability)
                                    </button>

                                    <div class="collapse mt-3" id="editCalendarDropdown">
                                        <div class="card border">
                                            <div class="card-body">

                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            id="edit-cal-prev">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </button>

                                                    <span id="edit-cal-label" class="fw-bold"></span>

                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            id="edit-cal-next">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </button>
                                                </div>

                                                <div id="edit-calendar" class="mini-calendar-grid"></div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- =======================
                        OTHER DETAILS
                    ======================== --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3" style="color:#6a4e32;">
                                Other Details
                            </h6>

                            <textarea name="remarks"
                                      id="edit-remarks"
                                      class="form-control"
                                      rows="3">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer d-flex justify-content-between border-0">

                    <button type="button"
                            id="reset-edit-form"
                            class="btn btn-outline-secondary rounded-pill px-4">
                        Reset Changes
                    </button>

                    <button type="submit" class="btn btn-coffee rounded-pill px-4">
                        Save Changes
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
