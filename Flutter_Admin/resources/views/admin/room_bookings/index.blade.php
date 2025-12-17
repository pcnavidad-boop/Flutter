<x-admin.layout title="Room Bookings">

    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Room Bookings</h2>

    {{-- FILTERS --}}
    <div class="content-card mb-4">
        <form method="GET" action="{{ route('admin.room_bookings.index') }}" class="filter-bar">

            <!-- LEFT FILTERS -->
            <div class="filter-controls">

                <input type="text" name="ref" value="{{ request('ref') }}"
                       class="form-control rounded-pill"
                       placeholder="Search reference (RB-XXXX)">

                <select name="status" class="form-select rounded-pill">
                    <option value="">All Status</option>
                    <option value="pending"     @selected(request('status')=='pending')>Pending</option>
                    <option value="confirmed"   @selected(request('status')=='confirmed')>Confirmed</option>
                    <option value="checked_in"  @selected(request('status')=='checked_in')>Checked-in</option>
                    <option value="checked_out" @selected(request('status')=='checked_out')>Checked-out</option>
                    <option value="cancelled"   @selected(request('status')=='cancelled')>Cancelled</option>
                </select>

                <select name="payment" class="form-select rounded-pill">
                    <option value="">All Payment</option>
                    <option value="unpaid"      @selected(request('payment')=='unpaid')>Unpaid</option>
                    <option value="downpayment" @selected(request('payment')=='downpayment')>Downpayment</option>
                    <option value="fully_paid"  @selected(request('payment')=='fully_paid')>Fully Paid</option>
                    <option value="refunded"    @selected(request('payment')=='refunded')>Refunded</option>
                </select>

                <button type="submit" class="btn btn-outline-coffee rounded-pill px-4">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <a href="{{ route('admin.room_bookings.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>
            </div>

            <!-- RIGHT BUTTON -->
            <button type="button" class="btn btn-coffee rounded-pill px-4"
                    data-bs-toggle="modal"
                    data-bs-target="#addRoomBookingModal">
                <i class="bi bi-plus-lg me-1"></i> Add Booking
            </button>

        </form>
    </div>

    {{-- TABLE --}}
    <div class="content-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->reference }}</strong></td>

                        <td>
                            <div>{{ $booking->guest_name }}</div>
                            <small class="text-muted">{{ $booking->guest_email }}</small>
                            @if($booking->guest_contact)
                                <small class="text-muted"> • {{ $booking->guest_contact }}</small>
                            @endif
                        </td>

                        <td>
                            <div>{{ $booking->room->name ?? '—' }}</div>

                            @if($booking->room && $booking->room->room_number)
                                <small class="text-muted">Room #{{ $booking->room->room_number }}</small>
                            @endif
                        </td>

                        <td>{{ $booking->period }}</td>

                        <td>
                            <span class="badge {{ $booking->booking_status_badge }}">
                                {{ $booking->booking_status_label }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $booking->payment_status_badge }}">
                                {{ $booking->payment_status_label }}
                            </span>
                        </td>

                        <td>₱{{ number_format($booking->total_price ?? 0, 2) }}</td>

                        <td class="text-end">
                            <button class="btn btn-sm btn-primary"
                                    data-id="{{ $booking->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewRoomBookingModal">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-warning"
                                    data-id="{{ $booking->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRoomBookingModal">
                                <i class="bi bi-pencil"></i>
                            </button>

                            @php
                                $hasPayments = $booking->payments()
                                    ->where('status','completed')
                                    ->exists();
                            @endphp

                            <button class="btn btn-sm btn-danger"
                                    data-id="{{ $booking->id }}"
                                    data-ref="{{ $booking->reference }}"
                                    data-has-payments="{{ $hasPayments ? '1' : '0' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCancelRoomBooking"
                                    @disabled(in_array($booking->booking_status, ['cancelled','checked_out','completed']))>
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            No bookings found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($bookings, 'links'))
            <div class="d-flex justify-content-end mt-3">
                {{ $bookings->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- MODALS --}}
    @include('admin.room_bookings.modals.add')
    @include('admin.room_bookings.modals.cancel')
    @include('admin.room_bookings.modals.edit')
    @include('admin.room_bookings.modals.view')

    {{-- CALENDAR STYLES --}}
    <style>
        .mini-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }
        .day { padding:6px; border-radius:6px; text-align:center; cursor:pointer; }
        .day.header { font-weight:700; cursor:default; }
        .day.past { background:#f1f1f1; color:#999; cursor:not-allowed; }
        .day.booked { background:#fdeaea; border:1px solid #e3b1b1; color:#8a4949; }
        .day.available { background:#e8f8ef; border:1px solid #cde9d6; }
        .day.selected { background:#ffecd1; border:2px solid #c38a42; }
        .day.in-range { background:#fff4de; color:#7a5a26; }
    </style>

    @push('scripts')
        <script src="/admin/js/pages/room_bookings/common.js" defer></script>
        <script src="/admin/js/pages/room_bookings/calendar.js" defer></script>
        <script src="/admin/js/pages/room_bookings/cancel.js" defer></script>
        <script src="/admin/js/pages/room_bookings/add.js" defer></script>
        <script src="/admin/js/pages/room_bookings/edit.js" defer></script>
        <script src="/admin/js/pages/room_bookings/view.js" defer></script>
        <script src="/admin/js/pages/room_bookings/init.js" defer></script>
    @endpush

    {{-- AUTO-OPEN ADD MODAL IF CONTROLLER SETS FLAG --}}
    @if (session('open_add_modal'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const m = new bootstrap.Modal(document.getElementById('addRoomBookingModal'));
                m.show();
            });
        </script>
    @endif

    {{-- AUTO-OPEN EDIT MODAL IF VALIDATION FAILED (repopulate with old() then fetch to fill missing) --}}
    @if (session('edit_id'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const id = "{{ session('edit_id') }}";
                const modal = new bootstrap.Modal(document.getElementById('editRoomBookingModal'));

                // Set form action early
                document.getElementById('edit-room-booking-form').action = `/admin/room-bookings/${id}`;

                // Fill with server-side old() values first (if present), otherwise fetch current booking
                const old = @json(old());

                // Helper to apply values safely
                function applyVals(obj) {
                    if (!obj) return;

                    if (old && Object.keys(old).length > 0) {
                        // apply old() values where available
                        document.getElementById('edit-guest-name').value = old.guest_name ?? obj.guest_name ?? "";
                        document.getElementById('edit-guest-email').value = old.guest_email ?? obj.guest_email ?? "";
                        document.getElementById('edit-guest-contact').value = old.guest_contact ?? obj.guest_contact ?? "";
                        document.getElementById('edit-guests').value = old.number_of_guests ?? obj.number_of_guests ?? 1;

                        // room id (select)
                        const roomSel = document.getElementById('edit-room-id');
                        if (roomSel) roomSel.value = old.room_id ?? obj.room?.id ?? "";

                        // type
                        const typeSel = document.getElementById('edit-type');
                        if (typeSel) typeSel.value = old.type ?? obj.type ?? 'walk-in';

                        // dates
                        document.getElementById('edit-start-date').value = old.start_date ?? obj.start_date ?? "";
                        document.getElementById('edit-end-date').value = old.end_date ?? obj.end_date ?? "";

                        document.getElementById('edit-remarks').value = old.remarks ?? obj.remarks ?? "";
                    } else {
                        // no old() → fill with fetched obj
                        document.getElementById('edit-guest-name').value = obj.guest_name ?? "";
                        document.getElementById('edit-guest-email').value = obj.guest_email ?? "";
                        document.getElementById('edit-guest-contact').value = obj.guest_contact ?? "";
                        document.getElementById('edit-guests').value = obj.number_of_guests ?? 1;

                        const roomSel = document.getElementById('edit-room-id');
                        if (roomSel) roomSel.value = obj.room?.id ?? "";

                        const typeSel = document.getElementById('edit-type');
                        if (typeSel) {
                            // If booking was 'website', keep the special logic from edit.js
                            if (obj.type === 'website') {
                                typeSel.innerHTML = `<option value="website">Website</option>`;
                                typeSel.disabled = true;
                            } else {
                                typeSel.disabled = false;
                                typeSel.value = obj.type ?? 'walk-in';
                            }
                        }

                        document.getElementById('edit-start-date').value = obj.start_date ?? "";
                        document.getElementById('edit-end-date').value = obj.end_date ?? "";
                        document.getElementById('edit-remarks').value = obj.remarks ?? "";
                    }

                    // reset email hint UI (matches edit.js behavior)
                    const emailInput = document.getElementById("edit-guest-email");
                    const hint = document.getElementById("edit-email-hint");
                    if (emailInput) emailInput.classList.remove("is-valid", "is-invalid");
                    if (hint) {
                        hint.innerText = "Enter a valid email address";
                        hint.style.color = "#6c757d";
                    }

                    // Show modal after populating fields
                    modal.show();

                    // fill calendar if available
                    if (window.editCal && typeof window.editCal.fill === 'function') {
                        try {
                            window.editCal.fill(
                                document.getElementById('edit-start-date')?.value,
                                document.getElementById('edit-end-date')?.value
                            );
                        } catch (e) { /* ignore */ }
                    }
                }

                // Attempt to fetch booking object to get canonical values (used when old() absent)
                fetch(`/admin/room-bookings/${id}`)
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(obj => applyVals(obj))
                    .catch(() => {
                        // If fetch fails, still try applying old() (if any)
                        applyVals({});
                    });

            });
        </script>
    @endif

</x-admin.layout>
