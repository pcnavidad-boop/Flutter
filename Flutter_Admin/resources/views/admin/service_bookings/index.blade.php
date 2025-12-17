<x-admin.layout title="Service Bookings">

    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Service Bookings</h2>

    {{-- FILTERS --}}
    <div class="content-card mb-4">
        <form method="GET" action="{{ route('admin.service_bookings.index') }}" class="filter-bar">

            <!-- LEFT FILTERS -->
            <div class="filter-controls">

                <input type="text" name="ref" value="{{ request('ref') }}"
                    class="form-control rounded-pill"
                    placeholder="Search reference (SB-XXXX)">

                <select name="status" class="form-select rounded-pill">
                    <option value="">All Status</option>
                    <option value="pending"    @selected(request('status')=='pending')>Pending</option>
                    <option value="confirmed"  @selected(request('status')=='confirmed')>Confirmed</option>
                    <option value="completed"  @selected(request('status')=='completed')>Completed</option>
                    <option value="cancelled"  @selected(request('status')=='cancelled')>Cancelled</option>
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

                <a href="{{ route('admin.service_bookings.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>
            </div>

            <!-- RIGHT BUTTON -->
            <button type="button" class="btn btn-coffee rounded-pill px-4"
                    data-bs-toggle="modal"
                    data-bs-target="#addServiceBookingModal">
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
                    <th>Service</th>
                    <th>Appointment</th>
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
                            <div>{{ $booking->service->name ?? '—' }}</div>
                            @if($booking->service)
                                <small class="text-muted">{{ $booking->service->price_label }}</small>
                            @endif
                        </td>

                        <td>
                            <div>{{ $booking->appointment_date?->format('M d, Y') ?? '—' }}</div>
                            @if($booking->time_slot)
                                <small class="text-muted">{{ $booking->time_slot }}</small>
                            @endif
                        </td>

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

                        <td>₱{{ $booking->formatted_price }}</td>

                        <td class="text-end">
                            <button class="btn btn-sm btn-primary"
                                    data-id="{{ $booking->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewServiceBookingModal">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-warning"
                                    data-id="{{ $booking->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editServiceBookingModal">
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
                                    data-bs-target="#modalCancelServiceBooking"
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
    @include('admin.service_bookings.modals.add')
    @include('admin.service_bookings.modals.cancel')
    @include('admin.service_bookings.modals.edit')
    @include('admin.service_bookings.modals.view')

    {{-- CALENDAR STYLES --}}
    <style>
        .mini-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }
        .day { padding:6px; border-radius:6px; text-align:center; cursor:pointer; font-size:0.9rem; }
        .day.header { font-weight:700; cursor:default; background:#f8f9fa; }
        .day.empty { cursor:default; }
        .day.past { background:#f1f1f1; color:#999; cursor:not-allowed; }
        .day.booked { background:#fdeaea; border:1px solid #e3b1b1; color:#8a4949; cursor:not-allowed; }
        .day.available { background:#e8f8ef; border:1px solid #cde9d6; transition:all 0.2s; }
        .day.available:hover { background:#d4f1df; transform:scale(1.05); }
        .day.selected { background:#ffecd1; border:2px solid #c38a42; font-weight:600; }
    </style>

    @push('scripts')
        <script src="/admin/js/pages/service_bookings/common.js" defer></script>
        <script src="/admin/js/pages/service_bookings/calendar.js" defer></script>
        <script src="/admin/js/pages/service_bookings/cancel.js" defer></script>
        <script src="/admin/js/pages/service_bookings/add.js" defer></script>
        <script src="/admin/js/pages/service_bookings/edit.js" defer></script>
        <script src="/admin/js/pages/service_bookings/view.js" defer></script>
        <script src="/admin/js/pages/service_bookings/init.js" defer></script>
    @endpush

    {{-- AUTO-OPEN ADD MODAL ON VALIDATION ERROR --}}
    @if (session('open_add_service_modal'))
        <script>
            window.__SERVICE_BOOKING__AUTOOPEN_ADD = true;
        </script>
    @endif

    {{-- AUTO-OPEN EDIT MODAL ON VALIDATION ERROR --}}
    @if (session('edit_service_id'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const id = "{{ session('edit_service_id') }}";
                const modal = new bootstrap.Modal(document.getElementById('editServiceBookingModal'));

                fetch(`/admin/service-bookings/${id}`)
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(d => {
                        const old = @json(old());

                        document.getElementById('edit-service-booking-form').action = `/admin/service-bookings/${id}`;
                        
                        // Populate fields with old() values or fetched data
                        document.getElementById('edit-guest-name').value = old.guest_name ?? d.guest_name ?? "";
                        document.getElementById('edit-guest-email').value = old.guest_email ?? d.guest_email ?? "";
                        document.getElementById('edit-guest-contact').value = old.guest_contact ?? d.guest_contact ?? "";
                        document.getElementById('edit-guests').value = old.number_of_guests ?? d.number_of_guests ?? 1;
                        document.getElementById('edit-appointment-date').value = old.appointment_date ?? d.appointment_date ?? "";
                        document.getElementById('edit-remarks').value = old.remarks ?? d.remarks ?? "";
                        
                        const serviceSelect = document.getElementById('edit-service-id');
                        if (serviceSelect) {
                            serviceSelect.value = old.service_id ?? d.service.id;
                            serviceSelect.dispatchEvent(new Event('change'));
                            
                            setTimeout(() => {
                                document.getElementById('edit-start-time').value = old.start_time ?? d.start_time ?? "";
                                if (old.start_time || d.start_time) {
                                    document.getElementById('edit-start-time').dispatchEvent(new Event('change'));
                                }
                            }, 100);
                        }

                        modal.show();
                    })
                    .catch(() => console.error('Failed to load booking for edit'));
            });
        </script>
    @endif

</x-admin.layout>