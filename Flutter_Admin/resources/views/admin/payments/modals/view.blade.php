<!-- VIEW PAYMENT MODAL -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color:#4a3426;">
                    Payment Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- LOADING --}}
                <div id="payment-view-loading" class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm"></div>
                    <div>Loading payment…</div>
                </div>

                {{-- CONTENT --}}
                <div id="payment-view-content" style="display:none;">

                    {{-- BASIC --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Basic Information
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-muted small">Payment Reference</div>
                                    <div id="pv-reference" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Booking Reference</div>
                                    <div id="pv-booking-ref" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Booking Type</div>
                                    <div id="pv-booking-type" class="fw-semibold"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PAYMENT --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Payment Details
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-muted small">Amount</div>
                                    <div class="fw-semibold">₱<span id="pv-amount"></span></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Method</div>
                                    <div id="pv-method" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Channel</div>
                                    <div id="pv-channel" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Status</div>
                                    <span id="pv-status" class="badge"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- META --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Metadata
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-muted small">Processed By</div>
                                    <div id="pv-processor" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Paid At</div>
                                    <div id="pv-paid-at" class="fw-semibold"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ERROR --}}
                <div id="payment-view-error"
                     class="alert alert-danger small"
                     style="display:none;">
                    Unable to load payment details.
                </div>

            </div>

        </div>
    </div>
</div>
