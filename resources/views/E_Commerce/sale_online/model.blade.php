<script src="https://cdn.tailwindcss.com"></script>
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 id="_oid" class="modal-title"></h4>
            </div>

            <div class="modal-body">
                <!-- Order Info -->
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <p><b>Order No:</b> <span id="modalOrderNo">#12345</span></p>
                        <p><b>Customer Name:</b> <span id="modalCustomerName">John Doe</span></p>
                        <p><b>Phone:</b> <span id="modalPhone">123-456-7890</span></p>
                        <p><b>Shipping Address:</b> <span id="modalAddress">123 Main St, City</span></p>
                    </div>
                    <div class="col-sm-6">
                        <p><b>Status:</b> <span id="modalStatus">Pending</span></p>
                        <p><b>Date:</b> <span id="modalDate">13/11/2025</span></p>
                        <p><b>Total:</b> <span id="modalTotal">$120.50</span></p>
                    </div>
                </div>

                <!-- Products Table -->
                <table class="table">
                    <thead class="bg-primary px-0">
                        <tr>
                            <th>Product</th>
                            <th>Price at Order</th>
                            <th>Qty</th>
                            <th>Total Line</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody id="modalProducts" class="bg-gray">
                    
                    </tbody>
                </table>
            </div>

            <div class="modal-footer flex justify-between">
                 <div id="conversionStatus" class="alert alert-success mb-3" style="display: none;"></div>
                <div>
                    <button type="button" class="btn bg-blue-500 text-white no-print" onclick="$(this).closest('div.modal').printThis();">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button id="convertToSaleBtn" class="btn bg-yellow-500 text-white no-print" data-order-id="">
                        Convert to Sell
                    </button>
                    <button id="convertAndOpenSaleBtn" class="btn bg-green-500 text-white no-print" data-order-id="">
                        Convert & Open Sell List
                    </button>
                    <button type="button" class="btn bg-gray-500 text-white no-print" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
