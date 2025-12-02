<!-- VIEW SERVICE MODAL -->
<div class="modal fade" id="modalViewService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Service Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="text-center mb-4">
                    <img id="viewServiceImage"
                         src=""
                         class="img-fluid rounded shadow"
                         style="max-height:260px;">
                </div>

                <div class="px-3">

                    <p><strong class="text-coffee">Name:</strong>
                        <span id="viewServiceName"></span>
                    </p>

                    <p><strong class="text-coffee">Type:</strong>
                        <span id="viewServiceType"></span>
                    </p>

                    <p><strong class="text-coffee">Location:</strong>
                        <span id="viewServiceLocation"></span>
                    </p>

                    <p>
                        <strong class="text-coffee">Price:</strong>
                        ₱<span id="viewServicePrice"></span>
                        <span id="viewServicePriceType" class="ms-1 text-muted"></span>
                    </p>

                    <p><strong class="text-coffee">Capacity:</strong>
                        <span id="viewServiceCapacity"></span>
                    </p>

                    <p><strong class="text-coffee">Operating Hours:</strong>
                        <span id="viewServiceHours"></span>
                    </p>

                    <p><strong class="text-coffee">Description:</strong></p>
                    <p id="viewServiceDescription"
                       class="ps-2 border-start"
                       style="border-color:#b8946b;"></p>

                </div>

            </div>

        </div>
    </div>
</div>
