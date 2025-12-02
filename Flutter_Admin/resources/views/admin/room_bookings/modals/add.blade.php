<div class="modal fade" id="addRoomBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Room Booking</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('admin.room_bookings.store') }}">
                @csrf

                <div class="modal-body">

                    {{-- ERRORS --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <!-- ============================= -->
                    <!-- 1. GUEST INFORMATION           -->
                    <!-- ============================= -->
                    <h6 class="fw-bold text-uppercase small text-muted mt-2">Guest Information</h6>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guest Name</label>
                            <input type="text" name="guest_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guest Email</label>
                            <input type="email"
                                name="guest_email"
                                id="guest-email"
                                class="form-control"
                                placeholder="name@example.com"
                                required
                                oninput="
                                        this.value = this.value.trim();
                                        validateEmailInput(this);
                                ">
                            <small class="text-muted" id="guest-email-hint">Enter a valid email address</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Number</label>
                            <input type="text"
                                name="guest_contact"
                                id="edit-guest-contact"
                                class="form-control"
                                maxlength="11"
                                pattern="^09\d{9}$"
                                placeholder="09XXXXXXXXX"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);">
                            <small class="text-muted">Format: 09XXXXXXXXX (optional)</small>
                        </div>

                    </div>


                    <!-- ============================= -->
                    <!-- 2. BOOKING ITEM               -->
                    <!-- ============================= -->
                    <h6 class="fw-bold text-uppercase small text-muted mt-4">Booking Details</h6>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room</label>
                            <select name="room_id" id="add-room-id" class="form-select" required>
                                <option value="">— Select Room —</option>
                                @foreach(\App\Models\Room::active()->available()->get() as $room)
                                    <option value="{{ $room->id }}">
                                        {{ $room->name }} — {{ $room->price_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guests</label>
                            <input type="number" name="number_of_guests"
                                   value="1" min="1"
                                   class="form-control" required>
                        </div>

                    </div>


                    <!-- ============================= -->
                    <!-- 3. SCHEDULE                    -->
                    <!-- ============================= -->
                    <h6 class="fw-bold text-uppercase small text-muted mt-4">Schedule</h6>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" id="add-start-date"
                                   class="form-control" readonly required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" id="add-end-date"
                                   class="form-control" readonly required>
                        </div>

                        <!-- Calendar Dropdown Button -->
                        <div class="col-12">
                            <button type="button"
                                    class="btn btn-outline-coffee rounded-pill w-100"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#addCalendarDropdown">
                                <i class="bi bi-calendar-event me-1"></i>
                                Check Availability
                            </button>

                            <div class="collapse mt-3" id="addCalendarDropdown">
                                <div class="card border">
                                    <div class="card-body">

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <button type="button" id="add-cal-prev"
                                                    class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-chevron-left"></i>
                                            </button>

                                            <span id="add-cal-label" class="fw-bold"></span>

                                            <button type="button" id="add-cal-next"
                                                    class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-chevron-right"></i>
                                            </button>
                                        </div>

                                        <div id="add-calendar" class="mini-calendar-grid"></div>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>


                    <!-- ============================= -->
                    <!-- 4. ADDITIONAL DETAILS          -->
                    <!-- ============================= -->
                    <h6 class="fw-bold text-uppercase small text-muted mt-4">Other Details</h6>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Booking Type</label>
                            <select name="type" class="form-select">
                                <option value="walk-in">Walk-in</option>
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-coffee rounded-pill px-4">Save Booking</button>
                </div>

            </form>

        </div>
    </div>
</div>
