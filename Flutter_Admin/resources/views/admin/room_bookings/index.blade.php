<x-admin.layout title="Room Bookings">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Room Bookings</h2>

    <!-- SEARCH + FILTER BAR CARD -->
    <div class="content-card mb-4">

        <form method="GET"
              action="{{ route('admin.room_bookings.index') }}"
              class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <!-- LEFT FILTERS -->
            <div class="d-flex align-items-center flex-wrap gap-2 filter-row">

                <!-- SEARCH -->
                <input type="text"
                       name="ref"
                       value="{{ request('ref') }}"
                       class="form-control rounded-pill filter-input"
                       placeholder="Search reference (RB-XXXX)"
                       style="min-width: 240px;">

                <!-- BOOKING STATUS -->
                <select name="status" class="form-select rounded-pill filter-input">
                    <option value="">All Status</option>
                    <option value="pending"     {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="confirmed"   {{ request('status')=='confirmed'?'selected':'' }}>Confirmed</option>
                    <option value="checked_in"  {{ request('status')=='checked_in'?'selected':'' }}>Checked-in</option>
                    <option value="checked_out" {{ request('status')=='checked_out'?'selected':'' }}>Checked-out</option>
                    <option value="cancelled"   {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                </select>

                <!-- PAYMENT STATUS -->
                <select name="payment" class="form-select rounded-pill filter-input">
                    <option value="">All Payment</option>
                    <option value="unpaid"       {{ request('payment')=='unpaid'?'selected':'' }}>Unpaid</option>
                    <option value="downpayment"  {{ request('payment')=='downpayment'?'selected':'' }}>Downpayment</option>
                    <option value="fully_paid"   {{ request('payment')=='fully_paid'?'selected':'' }}>Fully Paid</option>
                    <option value="refunded"     {{ request('payment')=='refunded'?'selected':'' }}>Refunded</option>
                </select>

                <!-- FILTER -->
                <button class="btn btn-outline-coffee rounded-pill px-4" type="submit">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <!-- RESET -->
                <a href="{{ route('admin.room_bookings.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>

            </div>

            <!-- ADD BOOKING -->
            <button class="btn btn-coffee rounded-pill px-4"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#addRoomBookingModal">
                <i class="bi bi-plus-lg me-1"></i> Add Booking
            </button>

        </form>
    </div>

    <!-- TABLE CARD -->
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

                    <tr @if($highlightRef === $booking->reference) style="background:#fff8e6;" @endif>

                        <td>
                            <strong>{{ $booking->reference }}</strong>
                        </td>

                        <td>
                            <div>{{ $booking->guest_name }}</div>
                            <small class="text-muted">
                                {{ $booking->guest_email }}
                                @if($booking->guest_contact)
                                    • {{ $booking->guest_contact }}
                                @endif
                            </small>
                        </td>

                        <td>{{ $booking->room->name ?? '—' }}</td>

                        <td>{{ $booking->period }}</td>

                        <td>
                            <span class="badge 
                                @if($booking->booking_status=='pending') bg-warning text-dark
                                @elseif($booking->booking_status=='cancelled') bg-secondary
                                @else bg-success @endif">
                                {{ ucfirst($booking->booking_status) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge 
                                @if($booking->payment_status=='unpaid') bg-secondary
                                @elseif($booking->payment_status=='refunded') bg-danger
                                @else bg-info text-dark @endif">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </td>

                        <td>₱{{ number_format($booking->total_price, 2) }}</td>

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

        <!-- PAGINATION -->
        @if(method_exists($bookings, 'links'))
            <div class="d-flex justify-content-end mt-3">
                {{ $bookings->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

    {{-- MODALS --}}
    @include('admin.room_bookings.modals.add')
    @include('admin.room_bookings.modals.edit')
    @include('admin.room_bookings.modals.view')

    {{-- ================= MINI CALENDAR STYLES ================= --}}
    <style>
        .mini-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
            background: #fff;
            min-height: 140px;
        }

        .mini-calendar-grid .day {
            padding: 6px;
            border-radius: 6px;
            text-align: center;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .day.header {
            font-weight: 700;
            cursor: default;
            background: transparent;
        }

        .day.past {
            color: #bbb;
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .day.booked {
            background: #fdeaea;
            color: #b64f4f;
            border: 1px solid #e3b1b1;
            cursor: not-allowed;
        }

        .day.available {
            background: #e8f8ef;
            color: #2f6642;
            border: 1px solid #cde9d6;
        }

        .day.selected {
            background: #ffefd4 !important;
            border: 2px solid #c59645 !important;
            color: #8a6220 !important;
        }

        .day.in-range {
            background: #fff4df;
            color: #7a5a26;
        }
    </style>

    @push('scripts')
    <script>
    /* =============================================================
       SIMPLE 2-CLICK CALENDAR (no hover logic, no advanced logic)
       ============================================================= */

    (function(){
        const weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        function daysInMonth(y,m){ return new Date(y, m+1, 0).getDate(); }
        function monthLabel(y,m){ return new Date(y, m).toLocaleString('default',{month:'long', year:'numeric'}); }
        function pad(n){ return String(n).padStart(2,'0'); }
        function iso(y,m,d){ return `${y}-${pad(m+1)}-${pad(d)}`; }

        function fetchAvailability(roomId, y, m){
            if (!roomId) return Promise.resolve({ booked_days:{} });
            const monthStr = `${y}-${pad(m+1)}`;
            return fetch(`/admin/room-bookings/check-availability/${roomId}?month=${monthStr}`)
                .then(r => r.ok ? r.json() : { booked_days:{} })
                .catch(()=>({ booked_days:{} }));
        }

        // main renderer
        function renderCalendar(opts){
            const { containerId, labelId, year, month, booked, startIso, endIso, onClick } = opts;
            const cont = document.getElementById(containerId);
            if (!cont) return;
            cont.innerHTML = '';

            // headers
            weekdays.forEach(w=>{
                const h=document.createElement('div');
                h.className="day header";
                h.innerText=w;
                cont.appendChild(h);
            });

            const firstDay = new Date(year,month,1).getDay();
            for(let i=0;i<firstDay;i++){
                const e=document.createElement('div'); e.className="day empty"; cont.appendChild(e);
            }

            const today = new Date();
            const todayIso = today.toISOString().slice(0,10);

            for(let d=1; d<=daysInMonth(year,month); d++){
                const cellIso = iso(year,month,d);
                const cellDate = new Date(cellIso);
                const el = document.createElement('div');
                el.className="day";
                el.innerText=d;

                // Past date?
                if(cellIso < todayIso){
                    el.classList.add("past");
                    cont.appendChild(el);
                    continue;
                }

                // booked?
                if(booked[cellIso]){
                    el.classList.add("booked");
                    cont.appendChild(el);
                    continue;
                }

                el.classList.add("available");

                // range highlight
                if(startIso && cellIso === startIso) el.classList.add("selected");
                if(endIso && cellIso === endIso) el.classList.add("selected");
                if(startIso && endIso && cellIso > startIso && cellIso < endIso){
                    el.classList.add("in-range");
                }

                // click
                el.addEventListener('click', ()=> onClick(cellIso));

                cont.appendChild(el);
            }

            document.getElementById(labelId).innerText = monthLabel(year,month);
        }

        // reusable instance
        function CalendarInstance(cfg){
            let { containerId, labelId, prevId, nextId, roomId, startInput, endInput, modalId } = cfg;

            let year = new Date().getFullYear();
            let month = new Date().getMonth();
            let bookedDays = {};
            let startDate = null;
            let endDate = null;

            function load(){
                const roomSel = document.getElementById(roomId);
                const room = roomSel ? roomSel.value : null;

                fetchAvailability(room, year, month).then(data=>{
                    bookedDays = data.booked_days || {};

                    renderCalendar({
                        containerId,
                        labelId,
                        year,
                        month,
                        booked: bookedDays,
                        startIso: startDate,
                        endIso: endDate,
                        onClick: handleClick
                    });
                });
            }

            function handleClick(clickedIso){
                // first click
                if(!startDate){
                    startDate = clickedIso;
                    endDate = null;
                    updateInputs();
                    load();
                    return;
                }

                // second click
                if(startDate && !endDate){
                    // if clicked before start → swap
                    if(clickedIso < startDate){
                        endDate = startDate;
                        startDate = clickedIso;
                    } else {
                        endDate = clickedIso;
                    }

                    // check conflict
                    let blocked = false;
                    let cur = new Date(startDate);
                    let last = new Date(endDate);
                    while(cur <= last){
                        const cIso = cur.toISOString().slice(0,10);
                        if(bookedDays[cIso]){
                            blocked = true;
                            break;
                        }
                        cur.setDate(cur.getDate()+1);
                    }

                    if(blocked){
                        // reset end
                        endDate = null;
                        showMessage("Selected range includes booked dates.", modalId);
                    } else {
                        updateInputs();
                    }

                    load();
                    return;
                }

                // clicking again starts new selection
                if(startDate && endDate){
                    startDate = clickedIso;
                    endDate = null;
                    updateInputs();
                    load();
                }
            }

            function updateInputs(){
                document.getElementById(startInput).value = startDate || "";
                document.getElementById(endInput).value = endDate || "";
            }

            // month navigation
            document.getElementById(prevId).addEventListener('click', ()=>{
                month--;
                if(month < 0){ month = 11; year--; }
                load();
            });

            document.getElementById(nextId).addEventListener('click', ()=>{
                month++;
                if(month > 11){ month = 0; year++; }
                load();
            });

            // reset when room changes
            document.getElementById(roomId).addEventListener('change', ()=>{
                startDate = null;
                endDate = null;
                updateInputs();
                load();
            });

            return {
                open(){
                    startDate = null;
                    endDate = null;
                    updateInputs();
                    load();
                },
                fill(startIso, endIso){
                    startDate = startIso || null;
                    endDate = endIso || null;
                    load();
                }
            };
        }

        function showMessage(msg, modalId){
            let modal = document.getElementById(modalId);
            if(!modal) return;
            let box = modal.querySelector(".calendar-msg");
            if(!box){
                box = document.createElement('div');
                box.className="calendar-msg mt-2";
                modal.querySelector('.modal-body').prepend(box);
            }
            box.innerHTML = `<div class="alert alert-danger py-2">${msg}</div>`;
            setTimeout(()=>box.remove(),2000);
        }

        // attach ADD calendar
        document.addEventListener('DOMContentLoaded', ()=>{
            const addCal = CalendarInstance({
                containerId: 'add-calendar',
                labelId: 'add-cal-label',
                prevId: 'add-cal-prev',
                nextId: 'add-cal-next',
                roomId: 'add-room-id',
                startInput: 'add-start-date',
                endInput: 'add-end-date',
                modalId: 'addRoomBookingModal'
            });

            document.getElementById('addRoomBookingModal')
                .addEventListener('shown.bs.modal', ()=> addCal.open());
        });

        // attach EDIT calendar
        document.addEventListener('DOMContentLoaded', ()=>{
            const editCal = CalendarInstance({
                containerId: 'edit-calendar',
                labelId: 'edit-cal-label',
                prevId: 'edit-cal-prev',
                nextId: 'edit-cal-next',
                roomId: 'edit-room-id',
                startInput: 'edit-start-date',
                endInput: 'edit-end-date',
                modalId: 'editRoomBookingModal'
            });

            document.getElementById('editRoomBookingModal')
                .addEventListener('shown.bs.modal', ()=>{
                    editCal.fill(
                        document.getElementById('edit-start-date').value,
                        document.getElementById('edit-end-date').value
                    );
                });
        });

    })();
    </script>
    <script>
    /* =============================================================
    IMPROVED EMAIL VALIDATION (Live UX)
    ============================================================= */

    function validateEmailInput(input, hintId) {
        const hint = document.getElementById(hintId);

        // Simple email pattern (acceptable & safe)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Remove spaces
        input.value = input.value.replace(/\s+/g, '');

        if (input.value === "") {
            input.classList.remove("is-invalid", "is-valid");
            hint.innerText = "Enter a valid email address";
            hint.style.color = "#6c757d";
            return;
        }

        if (!emailRegex.test(input.value)) {
            input.classList.add("is-invalid");
            input.classList.remove("is-valid");
            hint.innerText = "Invalid email format";
            hint.style.color = "#d9534f";
        } else {
            input.classList.add("is-valid");
            input.classList.remove("is-invalid");
            hint.innerText = "Looks good!";
            hint.style.color = "#198754";
        }
    }
    </script>
    @endpush

</x-admin.layout>
