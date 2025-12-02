<!-- ARCHIVE MODAL -->
<div class="modal fade" id="modalArchiveRoom" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="formArchiveRoom" method="POST">
                @csrf
                @method('PATCH')

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">Archive Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body text-center">
                    <p class="mb-3 fs-5">Change this room's archive status?</p>

                    <select id="archiveSelect"
                            name="is_archived"
                            class="form-select w-75 mx-auto">
                        <option value="1">Archive</option>
                        <option value="0">Unarchive</option>
                    </select>
                </div>

                <!-- FOOTER (BUTTON MATCHES SAVE ROOM & SAVE CHANGES BUTTONS) -->
                <div class="modal-footer justify-content-end">
                    <button class="btn btn-coffee">Update Status</button>
                </div>

            </form>

        </div>
    </div>
</div>
