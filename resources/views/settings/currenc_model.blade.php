<!-- Currency Modal -->
<div class="modal fade" id="currencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="currencyForm">
                @csrf
                <input type="hidden" id="currency_id" name="id">

                <div class="modal-header">
                    <h5 class="modal-title">Add Currency</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" id="country" name="country" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Currency</label>
                        <input type="text" id="currency" name="currency" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" id="code" name="code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Symbol</label>
                        <input type="text" id="symbol" name="symbol" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Thousand Separator</label>
                        <input type="text" id="thousand_separator" name="thousand_separator" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Decimal Separator</label>
                        <input type="text" id="decimal_separator" name="decimal_separator" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save-btn">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>

            </form>

        </div>
    </div>
</div>
