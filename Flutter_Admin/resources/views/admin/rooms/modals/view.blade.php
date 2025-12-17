<!-- VIEW ROOM MODAL -->
<div class="modal fade" id="modalViewRoom" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" style="color:#4a3426;">Room Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- IMAGE -->
                <div class="text-center mb-4">
                    <img id="viewRoomImage"
                         src="/admin/images/placeholder-room.png"
                         class="img-fluid rounded shadow-sm"
                         style="max-height: 260px;">
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
                                <div id="viewRoomName" class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Room Number</div>
                                <div id="viewRoomNumber" class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Type</div>
                                <div id="viewRoomType" class="fw-semibold text-capitalize"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- CAPACITY & BEDS -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Capacity & Beds
                        </h6>

                        <div class="row g-2">

                            <div class="col-md-6">
                                <div class="text-muted small">Capacity</div>
                                <div id="viewRoomCapacity" class="fw-semibold"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Beds</div>
                                <div id="viewRoomBeds" class="fw-semibold"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- PRICING -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Pricing
                        </h6>

                        <div class="row g-2">

                            <div class="col-md-6">
                                <div class="text-muted small">Price</div>
                                <div class="fw-semibold">
                                    ₱<span id="viewRoomPrice"></span>
                                    <small id="viewRoomPriceType" class="text-muted ms-1"></small>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- DESCRIPTION -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h6 class="text-uppercase small fw-bold mb-3" style="color:#6a4e32;">
                            Description
                        </h6>

                        <div id="viewRoomDescription"
                             class="fw-semibold ps-2 border-start"
                             style="border-color:#b8946b;">
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
