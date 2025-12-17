<!-- VIEW SERVICE MODAL -->
<div class="modal fade" id="modalViewService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color:#4a3426;">Service Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- IMAGE -->
                <div class="text-center mb-4">
                    <img id="viewServiceImage"
                         src=""
                         class="img-fluid rounded shadow-sm"
                         style="max-height:260px;">
                </div>

                <!-- BASIC INFORMATION -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Basic Information
                        </h6>

                        <div class="row g-2">

                            <div class="col-md-6">
                                <div class="text-muted small">Name</div>
                                <div id="viewServiceName" class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Type</div>
                                <div id="viewServiceType" class="fw-semibold text-capitalize"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Location</div>
                                <div id="viewServiceLocation" class="fw-semibold"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- SCHEDULE -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Schedule
                        </h6>

                        <div class="text-muted small">Hours</div>
                        <div id="viewServiceHours" class="fw-semibold"></div>

                    </div>
                </div>

                <!-- CAPACITY -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Capacity
                        </h6>

                        <div class="text-muted small">Capacity</div>
                        <div id="viewServiceCapacity" class="fw-semibold"></div>

                    </div>
                </div>

                <!-- PRICING -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Pricing
                        </h6>

                        <div class="text-muted small">Price</div>
                        <div class="fw-semibold">
                            ₱<span id="viewServicePrice"></span>
                            <small id="viewServicePriceType" class="text-muted ms-1">per person</small>
                        </div>

                    </div>
                </div>

                <!-- DESCRIPTION -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Description
                        </h6>

                        <div id="viewServiceDescription"
                             class="fw-semibold ps-2 border-start"
                             style="border-color:#b8946b;">
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
