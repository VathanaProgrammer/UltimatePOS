<script src="https://cdn.tailwindcss.com"></script>
<div class="modal fade" id="reward_Modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 id="_oid" class="modal-title">Invoice: Reward Details</h4>
                <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Customer & Date Info -->
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <p><b>Customer Name:</b> <span id="modalCustomerName">John Doe</span></p>
                    </div>
                    <div class="col-sm-6">
                        <p><b>Date:</b> <span id="modalDate">26/11/2025 09:00</span></p>
                        <p><b>Total:</b> <span id="modalTotal">$0.00</span></p>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total Line</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody id="modalProducts">
                            <!-- Dynamically filled -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer flex justify-end space-x-2">
                <button type="button" class="btn bg-blue-500 text-white no-print"
                    onclick="$(this).closest('div.modal').printThis();">
                    Print
                </button>
                <button type="button" class="btn bg-gray-500 text-white no-print" data-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // Example: format price and date in JS when filling modal
    function formatPrice(amount) {
        return '$' + (amount ? parseFloat(amount).toFixed(2) : '0.00');
    }

    function formatDate(datetime) {
        const dt = new Date(datetime);
        return dt.toLocaleDateString() + ' ' + dt.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
</script>
