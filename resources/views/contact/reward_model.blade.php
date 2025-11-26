<script src="https://cdn.tailwindcss.com"></script>
<div class="modal fade" id="reward_Modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 id="_oid" class="modal-title"></h4>
                <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Transaction Info -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p><b>Customer Name:</b> <span id="modalCustomerName">John Doe</span></p>
                    </div>
                    <div>
                        <p><b>Date:</b> <span id="modalDate">2025-11-26</span></p>
                        <p><b>Total:</b> <span id="modalTotal">$0.00</span></p>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="overflow-x-auto">
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
                        <tbody id="modalProducts"></tbody>
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
