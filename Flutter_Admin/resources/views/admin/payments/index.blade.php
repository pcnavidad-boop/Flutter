<x-admin.layout title="Payments">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">
        Payments
    </h2>

    <!-- SEARCH + FILTER BAR CARD -->
    <div class="content-card mb-4">

        <form method="GET"
              action="{{ route('admin.payments.index') }}"
              class="filter-bar d-flex justify-content-between align-items-center flex-wrap">

            <!-- LEFT FILTERS -->
            <div class="filter-controls d-flex gap-2 flex-wrap">

                <!-- SEARCH (PAYMENT / BOOKING REF) -->
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control rounded-pill"
                       placeholder="Search payment or booking ref...">

                <!-- METHOD -->
                <select name="method" class="form-select rounded-pill">
                    <option value="">All Methods</option>
                    @foreach (['cash','card','bank_transfer','e_wallet','api'] as $m)
                        <option value="{{ $m }}" @selected(request('method') === $m)>
                            {{ ucfirst(str_replace('_',' ', $m)) }}
                        </option>
                    @endforeach
                </select>

                <!-- STATUS -->
                <select name="status" class="form-select rounded-pill">
                    <option value="">All Status</option>
                    <option value="completed" @selected(request('status')==='completed')>
                        Completed
                    </option>
                    <option value="refunded" @selected(request('status')==='refunded')>
                        Refunded
                    </option>
                </select>

                <!-- CHANNEL -->
                <select name="channel" class="form-select rounded-pill">
                    <option value="">All Channels</option>
                    <option value="offline" @selected(request('channel')==='offline')>
                        Offline
                    </option>
                    <option value="online" @selected(request('channel')==='online')>
                        Online
                    </option>
                </select>

                <!-- FILTER BUTTON -->
                <button class="btn btn-outline-coffee rounded-pill px-4">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <!-- RESET -->
                <a href="{{ route('admin.payments.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>
            </div>

            <!-- RIGHT BUTTON -->
            <button class="btn btn-coffee rounded-pill px-4"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#addPaymentModal">
                <i class="bi bi-plus-lg me-1"></i>
                Add Payment
            </button>

        </form>
    </div>

    <!-- TABLE CARD -->
    <div class="content-card">

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Payment Ref</th>
                        <th>Booking Ref</th>
                        <th>Booking Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($payments as $payment)

                        @php
                            // DELETE RULES (must mirror backend)
                            $deleteReason = null;

                            if ($payment->method === 'api') {
                                $deleteReason = 'API payments cannot be deleted';
                            } elseif ($payment->channel === 'online') {
                                $deleteReason = 'Online payments cannot be deleted';
                            } elseif ($payment->status === 'refunded') {
                                $deleteReason = 'Refunded payments cannot be deleted';
                            }
                        @endphp

                        <tr>

                            <td class="fw-semibold">
                                {{ $payment->reference }}
                            </td>

                            <td>
                                {{ $payment->payable->reference ?? '—' }}
                            </td>

                            <td>
                                {{ class_basename($payment->payable) }}
                            </td>

                            <td>
                                ₱{{ $payment->formatted_amount }}
                            </td>

                            <td>
                                {{ ucfirst(str_replace('_',' ', $payment->method)) }}
                            </td>

                            <td>
                                @if ($payment->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Refunded</span>
                                @endif
                            </td>

                            <td>
                                {{ $payment->processor->name ?? 'System' }}
                            </td>

                            <td>
                                {{ $payment->formatted_date ?? '—' }}
                            </td>

                            <td class="text-end">

                                <!-- VIEW -->
                                <button class="btn btn-sm btn-primary"
                                        data-id="{{ $payment->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewPaymentModal">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- DELETE -->
                                <button class="btn btn-sm btn-danger"
                                        data-id="{{ $payment->id }}"
                                        data-ref="{{ $payment->reference }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDeletePayment"
                                        @disabled($deleteReason)
                                        title="{{ $deleteReason }}">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9"
                                class="text-center py-4 text-muted"
                                style="font-size:1.1rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                No payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION (future-proof) --}}
        @if (method_exists($payments, 'links'))
            <div class="d-flex justify-content-end mt-3">
                {{ $payments->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

    {{-- MODALS --}}
    @include('admin.payments.modals.add')
    @include('admin.payments.modals.view')
    @include('admin.payments.modals.delete')

    @push('scripts')
        <script src="/admin/js/pages/payments/add.js" defer></script>
        <script src="/admin/js/pages/payments/view.js" defer></script>
        <script src="/admin/js/pages/payments/delete.js" defer></script>
    @endpush

    {{-- AUTO-OPEN ADD PAYMENT MODAL ON VALIDATION ERROR --}}
    @if (session('open_add_payment_modal'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const modalEl = document.getElementById('addPaymentModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
    @endif

</x-admin.layout>
