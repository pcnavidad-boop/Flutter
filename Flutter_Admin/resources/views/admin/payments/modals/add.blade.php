<div class="modal fade" id="addPaymentModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Booking Reference --}}
                <div class="mb-3">
                    <label class="form-label">Booking Reference</label>
                    <input type="text" class="form-control" placeholder="e.g. RB-0001">
                </div>

                {{-- Payment Category --}}
                <div class="mb-3">
                    <label class="form-label">Payment Type</label>
                    <select class="form-select">
                        <option selected>Select</option>
                        <option>Downpayment</option>
                        <option>Full Payment</option>
                        <option>Refund</option> {{-- Used instead of refund modal --}}
                    </select>
                </div>

                {{-- Amount --}}
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" placeholder="Amount (use negative for refund)">
                </div>

                {{-- Method --}}
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select">
                        <option>Cash</option>
                        <option>Bank Transfer</option>
                        <option>Gcash</option>
                    </select>
                </div>

                <button class="btn btn-primary w-100">Save Payment</button>

            </div>

        </div>
    </div>
</div>
