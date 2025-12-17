<!-- ARCHIVE ROOM MODAL -->
<div class="modal fade" id="modalArchiveRoom" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow border-0">

            <form id="formArchiveRoom" method="POST">
                @csrf
                @method('PATCH')

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color:#4a3426;">Archive Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="mb-3 fs-5">
                        Change this room's archive status?
                    </p>

                    <select id="archiveSelect"
                            name="is_archived"
                            class="form-select w-75 mx-auto">
                        <option value="1">Archive</option>
                        <option value="0">Unarchive</option>
                    </select>
                </div>

                <div class="modal-footer border-0 justify-content-end">
                    <button class="btn btn-coffee">Update Status</button>
                </div>

            </form>

        </div>
    </div>
</div>
