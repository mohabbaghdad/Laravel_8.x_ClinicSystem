<div>
    <div class="modal fade" id="{{ 'did-not-attend-pop-up-user-'.$id }}">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attend</h5>
                    <button form="" type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Patient Has Not Attend
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button form="{{ 'did-not-attend'.$id }}" name="attend" value="false" type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
