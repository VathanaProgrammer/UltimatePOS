<div class="modal fade" id="add_reward_Modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Add Reward Points</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <p><b>Customer Name:</b> <span id="addPointsCustomerName">Customer</span></p>
                <div class="form-group">
                    <label for="pointsInput">Points to Add:</label>
                    <input type="number" id="pointsInput" class="form-control" min="1" placeholder="Enter points">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="savePointsBtn">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
