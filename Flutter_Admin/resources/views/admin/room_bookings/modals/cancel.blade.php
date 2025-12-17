<!-- CANCEL ROOM BOOKING MODAL -->
<div class="modal fade" id="modalCancelRoomBooking" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow border-0">

            <form id="formCancelRoomBooking" method="POST">
                @csrf
                @method('PATCH')

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">
                        Cancel Booking
                    </h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">

                    <p class="fs-5 mb-2">
                        Are you sure you want to cancel this booking?
                    </p>

                    <p class="text-muted small mb-0">
                        Booking Reference:
                        <strong id="cancelBookingRef">—</strong>
                    </p>

                    {{-- PAYMENT WARNING --}}
                    <div id="cancel-payment-warning"
                         class="alert alert-warning small mt-3 d-none">
                        This booking has recorded payments.<br>
                        <strong>Please refund all payments before cancelling.</strong>
                    </div>

                    <p class="text-danger small mt-3">
                        This action cannot be undone.
                    </p>
                </div>

                <div class="modal-footer border-0 justify-content-end">
                    <button type="button"
                            class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            id="btnConfirmCancel"
                            class="btn btn-danger rounded-pill px-4">
                        Yes, Cancel Booking
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
