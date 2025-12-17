<x-admin.layout title="Dashboard">

<div class="container-fluid">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">
        Dashboard
    </h2>

    <!-- =======================
         ROW 1 — KPI CARDS
    ======================== -->
    <div class="content-card mb-4">
        <div class="row g-3">

            <div class="col">
                <div class="text-center">
                    <div class="text-muted small">Pending Bookings (30d)</div>
                    <div class="display-6 fw-bold text-warning">
                        {{ $pendingBookings ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="text-center">
                    <div class="text-muted small">Confirmed Bookings (30d)</div>
                    <div class="display-6 fw-bold text-success">
                        {{ $confirmedBookings ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="text-center">
                    <div class="text-muted small">Cancelled Bookings (30d)</div>
                    <div class="display-6 fw-bold text-danger">
                        {{ $cancelledBookings ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="text-center">
                    <div class="text-muted small">Available Rooms</div>
                    <div class="display-6 fw-bold text-primary">
                        {{ $availableRooms ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="text-center">
                    <div class="text-muted small">Available Services</div>
                    <div class="display-6 fw-bold text-info">
                        {{ $availableServices ?? 0 }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- =======================
         ROW 2 — MAIN CONTENT
    ======================== -->
    <div class="row g-4">

        <!-- LEFT COLUMN — CHARTS -->
        <div class="col-lg-8">

            <!-- CHART 1 -->
            <div class="content-card mb-4">
                <h5 class="fw-semibold mb-3">
                    Confirmed Bookings per Type (Monthly)
                </h5>

                <canvas id="confirmedBookingsChart" height="120"></canvas>
            </div>

            <!-- CHART 2 -->
            <div class="content-card">
                <h5 class="fw-semibold mb-3">
                    Payments: Completed vs Refunded (Monthly)
                </h5>

                <canvas id="paymentsChart" height="120"></canvas>
            </div>

        </div>

        <!-- RIGHT COLUMN — RECENT BOOKINGS -->
        <div class="col-lg-4">

            <div class="content-card h-100">
                <h5 class="fw-semibold mb-3">Recent Bookings</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Guest Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                        @forelse ($recentBookings ?? [] as $booking)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $booking->reference }}
                                </td>
                                <td>
                                    {{ $booking->guest_name }}
                                </td>
                                <td>
                                    <span class="badge {{ $booking->booking_status_badge }}">
                                        {{ $booking->booking_status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    No recent bookings.
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- =======================
     CHART SCRIPTS
======================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* Confirmed Bookings Chart */
new Chart(document.getElementById('confirmedBookingsChart'), {
    type: 'bar',
    data: {
        labels: @json($confirmedBookingsMonthly['labels'] ?? []),
        datasets: [
            {
                label: 'Room Bookings',
                data: @json($confirmedBookingsMonthly['rooms'] ?? []),
                backgroundColor: 'rgba(13,110,253,0.7)',
                borderRadius: 5
            },
            {
                label: 'Service Bookings',
                data: @json($confirmedBookingsMonthly['services'] ?? []),
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

/* Payments Chart */
new Chart(document.getElementById('paymentsChart'), {
    type: 'bar',
    data: {
        labels: @json($paymentsMonthly['labels'] ?? []),
        datasets: [
            {
                label: 'Completed',
                data: @json($paymentsMonthly['completed'] ?? []),
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderRadius: 5
            },
            {
                label: 'Refunded',
                data: @json($paymentsMonthly['refunded'] ?? []),
                backgroundColor: 'rgba(220,53,69,0.7)',
                borderRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>

</x-admin.layout>
