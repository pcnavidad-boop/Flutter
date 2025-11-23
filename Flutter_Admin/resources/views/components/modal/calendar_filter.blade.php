<div class="modal fade" id="calendarFilterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Filter by Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('room_booking.check_availability') }}" method="POST">
                @csrf

                <div class="modal-body">

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                     </div>
                @endif

                    <div class="mb-3">
                        <label class="form-label">Check-in Date</label>
                        <input type="date" name="check_in_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Check-out Date</label>
                        <input type="date" name="check_out_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Number of Guests</label>
                        <input type="number" name="number_of_guests" class="form-control" required min="1">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Proceed</button>
                </div>
            </form>

        </div>
    </div>
</div>
