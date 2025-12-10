@extends('layouts.app')
@section('title', 'Catalog Details')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <header class="flex justify-between">
            <h1 class="text-3xl font-semibold text-gray-800">Currency</h1>
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" data-toggle="modal"
                data-target="#categoryModal">
                <i class="fa fa-plus mr-2"></i> Add Category
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
                            <th class="px-3 py-2">Symbol</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
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


            // Add Category form submission via AJAX
            $('#categoryForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('category_e.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        toastr.success(res.msg);
                        $('#categoryModal').modal('hide');
                        table.ajax.reload();
                        $('#categoryForm')[0].reset();
                    },
                    error: function(err) {
                        toastr.error(err.responseJSON?.msg || 'Failed to add category');
                    }
                });
            });

            // Open Edit Modal and prefill values
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                $('#categoryModal #category_id').val(id);
                $('#categoryModal #name').val($(this).data('name'));
                $('#categoryModal #description').val($(this).data('description'));
                $('#categoryModal #catalog_id').val($(this).data('catalog_id')).trigger('change');
                $('#categoryModal .modal-title').text('Edit Category');
                $('#categoryForm').attr('action', '{{ url('category_e') }}/' + id);
                $('#categoryModal').modal('show');
            });

            // Handle form submit for Add or Update dynamically
            $('#categoryForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                let actionUrl = $(this).attr('action');
                $.ajax({
                    url: actionUrl,
                    type: ($(this).find('#category_id').val()) ? 'PUT' : 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        toastr.success(res.msg);
                        $('#categoryModal').modal('hide');
                        table.ajax.reload();
                        $('#categoryForm')[0].reset();
                        $('#categoryModal .modal-title').text('Add Category');
                    },
                    error: function(err) {
                        toastr.error(err.responseJSON?.msg || 'Failed to save category');
                    }
                });
            });

            // Reset modal on close
            $('#categoryModal').on('hidden.bs.modal', function() {
                $('#categoryForm')[0].reset();
                $('#categoryForm').attr('action', '{{ route('category_e.store') }}');
                $('#categoryModal .modal-title').text('Add Category');
            });
        });
    </script>
@endsection
