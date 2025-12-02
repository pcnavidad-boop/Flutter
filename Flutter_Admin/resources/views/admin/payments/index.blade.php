<x-admin.layout title="Payments">

    <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Payments</h2>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="bi bi-cash-coin me-1"></i> Add Payment
            </button>
        </div>

        {{-- Search --}}
        <div class="input-group mb-3" style="max-width: 350px;">
            <input type="text" class="form-control" placeholder="Search by booking reference...">
            <button class="btn btn-outline-secondary">Search</button>
        </div>

        {{-- Payments Table --}}
        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Booking Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Date</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        {{-- Placeholder row --}}
                        <tr>
                            <td>RB-0001</td>
                            <td>Room Booking</td>
                            <td>₱2,000</td>
                            <td>Cash</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>Admin User</td>
                            <td>Jan 15, 2025</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewPaymentModal">View</button>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>
                            </td>
                        </tr>

                        <tr>
                            <td>RB-0001</td>
                            <td>Room Booking</td>
                            <td class="text-danger">₱-2,000</td>
                            <td>Cash</td>
                            <td><span class="badge bg-danger">Refunded</span></td>
                            <td>Admin User</td>
                            <td>Jan 16, 2025</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewPaymentModal">View</button>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>
        </div>

    </div>

    {{-- Modals --}}
    @include('admin.payments.modals.add')
    @include('admin.payments.modals.view')

</x-admin.layout>
