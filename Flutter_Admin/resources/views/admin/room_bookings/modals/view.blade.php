<div class="modal fade" id="viewRoomBookingModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Booking Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div id="view-loading" class="text-center py-4 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm"></div>
                    <div>Loading booking...</div>
                </div>

                <div id="view-content" style="display:none;">

                    <p><strong>Reference:</strong> <span id="view-ref"></span></p>

                    <p><strong>Guest:</strong><br>
                        <span id="view-guest"></span><br>
                        <small id="view-contact" class="text-muted"></small>
                    </p>

                    <p><strong>Room:</strong> <span id="view-room"></span></p>

                    <p><strong>Dates:</strong><br>
                        <span id="view-dates"></span>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <span id="view-status" class="badge"></span>
                    </p>

                    <p>
                        <strong>Payment:</strong>
                        <span id="view-payment" class="badge"></span>
                    </p>

                    <p>
                        <strong>Total Price:</strong>
                        ₱<span id="view-total"></span>
                    </p>

                    <p>
                        <strong>Remarks:</strong><br>
                        <span id="view-remarks"></span>
                    </p>

                    <hr>

                    <p class="text-muted small mb-0">
                        <strong>Created:</strong> <span id="view-created"></span>
                    </p>

                </div>

                <div id="view-error" class="alert alert-danger mt-2 py-2 small" style="display:none;">
                    Unable to load booking details.
                </div>

            </div>

        </div>
    </div>
</div>

{{-- ================= VIEW MODAL AJAX ================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('viewRoomBookingModal');

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const bookingId = button.getAttribute('data-id');

        // Reset sections
        document.getElementById('view-loading').style.display = 'block';
        document.getElementById('view-content').style.display = 'none';
        document.getElementById('view-error').style.display = 'none';

        fetch(`/admin/room-bookings/${bookingId}`)
            .then(r => r.json())
            .then(data => {

                document.getElementById('view-ref').innerText = data.reference;
                document.getElementById('view-guest').innerText = data.guest_name + " (" + data.guest_email + ")";
                document.getElementById('view-contact').innerText = data.guest_contact ?? "—";

                document.getElementById('view-room').innerText = data.room.name;

                document.getElementById('view-dates').innerText =
                    data.start_date + " → " + data.end_date;

                // status
                let s = document.getElementById('view-status');
                s.innerText = capitalize(data.booking_status);
                s.className = "badge " + (
                    data.booking_status === 'pending'
                        ? "bg-warning text-dark"
                        : "bg-success"
                );

                // payment
                let p = document.getElementById('view-payment');
                p.innerText = capitalize(data.payment_status);
                p.className = "badge " + (
                    data.payment_status === 'unpaid'
                        ? "bg-secondary"
                        : "bg-info"
                );

                document.getElementById('view-total').innerText =
                    parseFloat(data.total_price).toFixed(2);

                document.getElementById('view-remarks').innerText =
                    data.remarks ?? "None";

                document.getElementById('view-created').innerText =
                    data.created_at;

                document.getElementById('view-loading').style.display = 'none';
                document.getElementById('view-content').style.display = 'block';
            })
            .catch(() => {
                document.getElementById('view-loading').style.display = 'none';
                document.getElementById('view-error').style.display = 'block';
            });
    });

    function capitalize(str){
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
});
</script>
