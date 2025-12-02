<div class="modal fade" id="addServiceBookingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">Add Service Booking</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="Guest Name">
                    </div>

                    <div class="col-md-6">
                        <input type="email" class="form-control" placeholder="Email">
                    </div>

                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="Contact Number">
                    </div>

                    <div class="col-md-6">
                        <select class="form-select">
                            <option selected>Select Service</option>
                            <option>Spa Treatment</option>
                            <option>Pool Access</option>
                            <option>Gym Session</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <input type="date" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <input type="time" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <input type="number" class="form-control" placeholder="Guests Count">
                    </div>

                    <div class="col-md-12">
                        <textarea class="form-control" rows="3" placeholder="Remarks"></textarea>
                    </div>

                </div>

                <button class="btn btn-primary mt-3 w-100">Save Booking</button>

            </div>

        </div>
    </div>
</div>
