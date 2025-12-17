<!-- ADD PAYMENT MODAL -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <form method="POST"
                  action="{{ route('admin.payments.store') }}">
                @csrf

                {{-- HEADER --}}
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">
                        Add Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- SUMMARY ERROR --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            Please fix the highlighted errors below.
                        </div>
                    @endif

                    {{-- BOOKING --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3"
                                style="color:#6a4e32;">
                                Booking
                            </h6>

                            <label class="form-label text-muted small">
                                Booking Reference
                            </label>

                            <input type="text"
                                   name="booking_reference"
                                   class="form-control @error('booking_reference') is-invalid @enderror"
                                   value="{{ old('booking_reference') }}"
                                   placeholder="RB-XXXXXXXX or SB-XXXXXXXX"
                                   required>

                            @error('booking_reference')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- PAYMENT SUMMARY --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3"
                                style="color:#6a4e32;">
                                Payment Summary
                            </h6>

                            <div class="row text-center">
                                <div class="col">
                                    <small class="text-muted">Total</small>
                                    <div class="fw-bold" id="summary-total">
                                        ₱0.00
                                    </div>
                                </div>

                                <div class="col">
                                    <small class="text-muted">Paid</small>
                                    <div class="fw-bold" id="summary-paid">
                                        ₱0.00
                                    </div>
                                </div>

                                <div class="col">
                                    <small class="text-muted">Remaining</small>
                                    <div class="fw-bold text-danger"
                                         id="summary-remaining">
                                        ₱0.00
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PAYMENT DETAILS --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-uppercase fw-bold small mb-3"
                                style="color:#6a4e32;">
                                Payment Details
                            </h6>

                            <div class="row g-3">

                                {{-- STATUS --}}
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">
                                        Status
                                    </label>

                                    <select name="status"
                                            class="form-select @error('status') is-invalid @enderror"
                                            required>
                                        <option value="completed"
                                            @selected(old('status') === 'completed')>
                                            Completed
                                        </option>

                                        <option value="refunded"
                                            @selected(old('status') === 'refunded')>
                                            Refunded
                                        </option>
                                    </select>

                                    @error('status')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- AMOUNT --}}
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">
                                        Amount
                                    </label>

                                    <input type="number"
                                           name="amount"
                                           min="0.01"
                                           step="0.01"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}"
                                           required>

                                    @error('amount')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <small class="text-muted small d-block mt-1">
                                        Suggested downpayment (30%):
                                        <span id="suggested-downpayment">
                                            ₱0.00
                                        </span>
                                    </small>
                                </div>

                                {{-- METHOD --}}
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">
                                        Method
                                    </label>

                                    <select name="method"
                                            class="form-select @error('method') is-invalid @enderror"
                                            required>
                                        <option value="cash"
                                            @selected(old('method') === 'cash')>
                                            Cash
                                        </option>
                                        <option value="card"
                                            @selected(old('method') === 'card')>
                                            Card
                                        </option>
                                        <option value="bank_transfer"
                                            @selected(old('method') === 'bank_transfer')>
                                            Bank Transfer
                                        </option>
                                        <option value="e_wallet"
                                            @selected(old('method') === 'e_wallet')>
                                            E-Wallet
                                        </option>
                                        <option value="api"
                                            @selected(old('method') === 'api')>
                                            API / Online
                                        </option>
                                    </select>

                                    @error('method')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer border-0 justify-content-between">
                    <button type="button"
                            class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-coffee rounded-pill px-4">
                        Save Payment
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
