<div class="modal fade" id="editRoomBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editRoomBookingForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="edit_booking_id" name="booking_id">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Guest Name</label>
                        <input type="text" id="edit_guest_name" name="guest_name" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Guest Email</label>
                        <input type="email" id="edit_guest_email" name="guest_email" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Guest Contact</label>
                        <input type="text" id="edit_guest_contact" name="guest_contact" class="form-control" required>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Check-In Date</label>
                        <input type="date" id="edit_check_in_date" name="check_in_date" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Check-Out Date</label>
                        <input type="date" id="edit_check_out_date" name="check_out_date" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea id="edit_remarks" name="remarks" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status Change Reason</label>
                    <textarea id="edit_status_change_reason" name="status_change_reason" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Booking Status</label>
                        <select id="edit_booking_status" name="booking_status" class="form-select" required>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Declined">Declined</option>
                            <option value="Checked_In">Checked In</option>
                            <option value="Checked_Out">Checked Out</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Payment Status</label>
                        <select id="edit_payment_status" name="payment_status" class="form-select" required>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partially_Paid">Partially Paid</option>
                            <option value="Paid">Paid</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id='editForm' class="btn btn-success">Update Booking</button>
            </div>

        </form>
    </div>
</div>
