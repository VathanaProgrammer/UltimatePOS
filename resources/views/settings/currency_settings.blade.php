@extends('layouts.app')
@section('title', 'Catalog Details')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <header class="flex justify-between">
            <h1 class="text-3xl font-semibold text-gray-800">Currency</h1>
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-toggle="modal"
                data-target="#categoryModal">
                <i class="fa fa-plus mr-2"></i> Add Currency
            </button>
        </header>

        <div class="shadow-md bg-white p-6 mt-6">
            <div class="relative overflow-x-auto">
                <table id="categories_table" class="w-full text-sm text-left text-gray-700">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2">Country</th>
                            <th class="px-3 py-2">Currency</th>
                            <th class="px-3 py-2">Exchange Rate</th>
                            <th class="px-3 py-2">Code</th>
                            <th class="px-3 py-2">Symbol</th>
                            <th class="px-3 py-2">Thousand Separator</th>
                            <th class="px-3 py-2">Decimal Separator</th>
                            <th class="px-3 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
    @include('settings.delete_model')
    @include('settings.currenc_model')
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('#categories_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('currencies.data') }}',
                columns: [{
                        data: 'country',
                        title: "Country"
                    },
                    {
                        data: 'currency',
                        title: "Currency"
                    },
                    {
                        data: 'exchange_rate',
                        title: "Exchange Rate"
                    },

                    {
                        data: 'code',
                        title: "Code"
                    },
                    {
                        data: 'symbol',
                        title: "Symbol"
                    },
                    {
                        data: 'thousand_separator',
                        title: "Thousand Separator"
                    },
                    {
                        data: 'decimal_separator',
                        title: "Decimal Separator"
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // ----------------------
            // OPEN EDIT MODAL
            // ----------------------
            $(document).on('click', '.edit-btn', function() {
                $('#currency_id').val($(this).data('id'));
                $('#country').val($(this).data('country'));
                $('#currency').val($(this).data('currency'));
                $('#code').val($(this).data('code'));
                $('#symbol').val($(this).data('symbol'));
                $('#thousand_separator').val($(this).data('thousand'));
                $('#decimal_separator').val($(this).data('decimal'));
                $('.modal-title').text("Edit Currency");
                $('#exchange_rate').val($(this).data('exchange_rate'));

                $('#currencyForm').attr('action', '/currency/update/' + $(this).data('id'));
            });

            $('#currencyForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#currency_id').val();
                let url = '/currency/update/' + id;

                let $btn = $(this).find('.save-btn');
                $btn.prop('disabled', true); // Disable during AJAX


                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#currencyModal').modal('hide');
                            $('#categories_table').DataTable().ajax.reload(null, false);

                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            // show ALL validation errors
                            $.each(errors, function(field, messages) {
                                toastr.error(messages[0]);
                            });

                        } else {
                            toastr.error("Failed to update currency");
                        }

                        $btn.prop('disabled', false);
                    }
                });
            });

            // ----------------------
            // OPEN DELETE MODAL
            // ----------------------
            $(document).on('click', '.delete-btn', function() {
                $('#delete_id').val($(this).data('id'));
            });


            // ----------------------
            // CONFIRM DELETE
            // ----------------------
            // Confirm delete
            $('#confirmDelete').click(function() {
                let id = $('#delete_id').val();

                $.ajax({
                    url: '/currency/delete/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        $('#deleteModal').modal('hide');
                        toastr.success('Currency deleted');
                        $('#categories_table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Failed to delete currency");
                    }
                });
            });
        });
    </script>
@endsection
