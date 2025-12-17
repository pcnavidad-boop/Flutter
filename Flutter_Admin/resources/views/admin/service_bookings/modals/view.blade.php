{{-- resources/views/admin/service_bookings/modals/view.blade.php --}}

<div class="modal fade" id="viewServiceBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color:#4a3426;">Service Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- LOADING --}}
                <div id="view-loading" class="py-4 text-center text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm"></div>
                    <div>Loading booking...</div>
                </div>

                {{-- CONTENT --}}
                <div id="view-content" style="display:none;">

                    {{-- REFERENCE --}}
                    <div class="mb-4">
                        <div class="small text-uppercase fw-bold mb-2" style="color:#6a4e32;">
                            Booking Reference
                        </div>

                        <div class="d-inline-flex align-items-center bg-light border rounded px-3 py-2 shadow-sm"
                             style="gap:12px;">

                            <span id="view-ref"
                                  class="fw-bold"
                                  style="font-family: monospace; letter-spacing:.5px;"></span>

                            <button id="copy-ref-btn"
                                    class="btn btn-sm border-0 rounded px-2 py-1"
                                    style="background:#e9ecef;"
                                    onclick="navigator.clipboard.writeText(document.getElementById('view-ref').innerText)">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    {{-- GUEST --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">

                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Guest Information
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-muted small">Name</div>
                                    <div id="view-guest-name" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Email</div>
                                    <div id="view-guest-email" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Contact</div>
                                    <div id="view-contact" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Total Guests</div>
                                    <div id="view-num-guests" class="fw-semibold"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- SERVICE --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">

                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Service Information
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-muted small">Service Name</div>
                                    <div id="view-service-name" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Location</div>
                                    <div id="view-service-location" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-12">
                                    <div class="text-muted small">Service Type</div>
                                    <div id="view-service-type" class="fw-semibold"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- APPOINTMENT --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Appointment Details
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-muted small">Date</div>
                                    <div id="view-date" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Time Slot</div>
                                    <div id="view-time" class="fw-semibold"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                                Status Summary
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="text-muted small">Booking Status</div>
                                    <span id="view-status" class="badge px-3 py-2 fw-semibold"></span>
                                </div>

                                <div class="col-md-4">
                                    <div class="text-muted small">Payment Status</div>
                                    <span id="view-payment" class="badge px-3 py-2 fw-semibold"></span>
                                </div>

                                <div class="col-md-4">
                                    <div class="text-muted small">Total Price</div>
                                    <div class="fw-bold text-dark">₱<span id="view-total"></span></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- REMARKS --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">

                            <h6 class="text-uppercase small fw-bold mb-2" style="color:#6a4e32;">
                                Remarks
                            </h6>

                            <div id="view-remarks" class="fw-semibold"></div>
                        </div>
                    </div>

                    {{-- META --}}
                    <div class="text-muted small">
                        <p class="mb-1">
                            <strong>Created:</strong>
                            <span id="view-created"></span>
                        </p>

                        <p class="mb-0">
                            <strong>Created By:</strong>
                            <span id="view-created-by"></span>
                        </p>
                    </div>

                </div>

                {{-- ERROR --}}
                <div id="view-error"
                     class="alert alert-danger small mt-3"
                     style="display:none;">
                    Unable to load booking details.
                </div>

            </div>
        </div>
    </div>
</div>