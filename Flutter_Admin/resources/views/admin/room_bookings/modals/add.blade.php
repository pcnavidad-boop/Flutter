{{-- resources/views/admin/room_bookings/modals/add.blade.php --}}

<div class="modal fade" id="addRoomBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color:#4a3426;">Add Room Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST"
                  id="formAddBooking"
                  action="{{ route('admin.room_bookings.store') }}">
                @csrf

                <div class="modal-body">

                    {{-- Display validation errors (global) --}}
                    @if($errors->any() && session('open_add_modal'))
                        <div class="alert alert-danger small">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- =======================
                        GUEST INFORMATION
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
                                           class="form-control"
                                           value="{{ old('guest_name') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Email</label>
                                    <input  type="email"
                                            name="guest_email"
                                            id="add-guest-email"
                                            class="form-control"
                                            value="{{ old('guest_email') }}"
                                            placeholder="name@example.com"
                                            required>
                                    <small id="add-email-hint"
                                           class="text-muted d-block mt-1">
                                        Enter a valid email address
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Contact Number</label>
                                    <input  type="text"
                                            name="guest_contact"
                                            id="add-guest-contact"
                                            class="form-control"
                                            maxlength="11"
                                            value="{{ old('guest_contact') }}"
                                            placeholder="09XXXXXXXXX"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);">
                                    <small class="text-muted">Format: 09XXXXXXXXX (optional)</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Guests</label>
                                    <input type="number"
                                           name="number_of_guests"
                                           value="{{ old('number_of_guests', 1) }}"
                                           min="1"
                                           class="form-control"
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
                                            id="add-room-id"
                                            class="form-select"
                                            required>
                                        <option value="">— Select Room —</option>

                                        {{-- Use model scope directly so the list is accurate --}}
                                        @foreach(\App\Models\Room::active()->available()->get() as $room)
                                            <option value="{{ $room->id }}"
                                                @selected(old('room_id') == $room->id)>
                                                {{ $room->name }} — {{ $room->price_label }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Booking Type</label>
                                    <select name="type" class="form-select">
                                        <option value="walk-in" @selected(old('type') == 'walk-in')>Walk-in</option>
                                        <option value="phone" @selected(old('type') == 'phone')>Phone</option>
                                        <option value="email" @selected(old('type') == 'email')>Email</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- =======================
                        SCHEDULE + CALENDAR
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
                                           name="start_date"
                                           id="add-start-date"
                                           class="form-control"
                                           readonly
                                           value="{{ old('start_date') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small">End Date</label>
                                    <input type="date"
                                           name="end_date"
                                           id="add-end-date"
                                           class="form-control"
                                           readonly
                                           value="{{ old('end_date') }}"
                                           required>
                                </div>

                                <div class="col-12">
                                    <button type="button" class="btn btn-outline-coffee rounded-pill w-100"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#addCalendarDropdown">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Check Availability
                                    </button>

                                    <div class="collapse mt-3" id="addCalendarDropdown">
                                        <div class="card border">
                                            <div class="card-body">

                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            id="add-cal-prev">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </button>

                                                    <span id="add-cal-label" class="fw-bold"></span>

                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            id="add-cal-next">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </button>
                                                </div>

                                                <div id="add-calendar" class="mini-calendar-grid"></div>

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
                                      class="form-control"
                                      rows="3">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer d-flex justify-content-between border-0">
                    <button type="button"
                            id="clear-add-form"
                            class="btn btn-outline-secondary rounded-pill px-4">
                        Clear Form
                    </button>

                    <button type="submit" class="btn btn-coffee rounded-pill px-4">
                        Save Booking
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
