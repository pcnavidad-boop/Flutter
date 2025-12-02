<x-admin.layout title="Dashboard">

    <div class="container-fluid">

        <h2 class="fw-bold mb-4">Dashboard</h2>

        {{-- Simple placeholder stats --}}
        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Total Rooms</h6>
                    <div class="display-6 text-primary">--</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Pending Bookings</h6>
                    <div class="display-6 text-warning">--</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Today’s Arrivals</h6>
                    <div class="display-6 text-success">--</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Today’s Departures</h6>
                    <div class="display-6 text-danger">--</div>
                </div>
            </div>

        </div>

        {{-- Chart placeholder --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Bookings Overview</h5>

                <div class="border rounded text-center p-5 bg-light" style="height: 250px;">
                    <span class="text-muted">Chart Placeholder</span>
                </div>
            </div>
        </div>

        {{-- Placeholder recent activity --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Recent Activity</h5>

                <ul class="list-group">
                    <li class="list-group-item text-muted">No recent activity.</li>
                </ul>
            </div>
        </div>

    </div>

</x-admin.layout>
