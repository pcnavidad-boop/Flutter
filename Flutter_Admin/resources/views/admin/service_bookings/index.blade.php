<x-admin.layout title="Service Bookings">

    <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Service Bookings</h2>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceBookingModal">
                <i class="bi bi-plus-lg me-1"></i> Add Booking
            </button>
        </div>

        {{-- Search --}}
        <div class="input-group mb-3" style="max-width: 350px;">
            <input type="text" class="form-control" placeholder="Search by reference...">
            <button class="btn btn-outline-secondary">Search</button>
        </div>

        {{-- Table --}}
        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        {{-- Placeholder row --}}
                        <tr>
                            <td>SB-0001</td>
                            <td>Jane Doe</td>
                            <td>Spa Treatment</td>
                            <td>Jan 22, 2025</td>
                            <td>2:00 PM</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td><span class="badge bg-secondary">Unpaid</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewServiceBookingModal">View</button>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editServiceBookingModal">Edit</button>
                            </td>
                        </tr>

                        <tr>
                            <td>SB-0002</td>
                            <td>Michael Cruz</td>
                            <td>Pool Access</td>
                            <td>Jan 25, 2025</td>
                            <td>10:00 AM</td>
                            <td><span class="badge bg-success">Confirmed</span></td>
                            <td><span class="badge bg-info">Downpayment</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewServiceBookingModal">View</button>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editServiceBookingModal">Edit</button>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

    {{-- Modals --}}
    @include('admin.service_bookings.modals.add')
    @include('admin.service_bookings.modals.view')
    @include('admin.service_bookings.modals.edit')

</x-admin.layout>
