<!-- DELETE PAYMENT MODAL -->
<div class="modal fade" id="modalDeletePayment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow border-0">

            <form id="formDeletePayment" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">
                        Delete Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">

                    <p class="fs-5 mb-2">
                        Delete this payment?
                    </p>

                    <p class="text-muted small mb-3">
                        Payment Reference:
                        <strong id="deletePaymentReference">—</strong>
                    </p>

                    <p class="text-danger small">
                        This will rollback the payment and update the booking balance.
                    </p>

                </div>

                <div class="modal-footer border-0 justify-content-end">
                    <button type="button"
                            class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-danger rounded-pill px-4">
                        Delete Payment
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
