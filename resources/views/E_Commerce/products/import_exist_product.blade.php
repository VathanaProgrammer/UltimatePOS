@extends('layouts.app')
@section('title', __('Import Existing E-Commerce Products'))
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Import Products</h1>

        <div class="w-full min-h-[400px] rounded bg-white mt-4 p-4 shadow-md">
            <header class="flex mb-4 gap-4 items-end justify-between">
                <div class="form-group min-w-[300px]">
                    {!! Form::label('Category', 'Select Category:*', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) !!}
                    {!! Form::select('category_id', $categories, null, [
                        'class' => 'form-control select2',
                        'id' => 'filter_category',
                        'placeholder' => 'Choose a category',
                    ]) !!}
                </div>

                <div class="flex gap-2 items-end">
                    <button id="import_selected"
                        class="px-4 py-2 bg-blue-600 text-white min-w-[130px] rounded hover:bg-blue-700 text-md font-medium">
                        <i class="fa fa-download mr-2"></i> Import
                    </button>
                </div>
            </header>

            <form id="importForm">
                @csrf
                <table id="products_table" class="w-full text-left table-auto">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-2 py-2"><input type="checkbox" id="select_all"></th>
                            <th class="px-2 py-2">Product image</th>
                            <th class="px-2 py-2">Action</th>
                            <th class="px-2 py-2">Product</th>
                            <th class="px-2 py-2">Business Location</th>
                            <th class="px-2 py-2">Unit Purchase Price</th>
                            <th class="px-2 py-2">Unit Selling Price</th>
                            <th class="px-2 py-2">Current Stock</th>
                            <th class="px-2 py-2">Category</th>
                            <th class="px-2 py-2">Brand</th>
                            <th class="px-2 py-2">SKU</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </form>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('#products_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('importExistingProduct.data') }}',
                columns: [{
                        data: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'business_location',
                        defaultContent: '--'
                    },
                    {
                        data: 'unit_purchase_price'
                    },
                    {
                        data: 'unit_selling_price'
                    },
                    {
                        data: 'total_stock'
                    },
                    {
                        data: 'category_name',
                        defaultContent: '--'
                    },
                    {
                        data: 'brand_name',
                        defaultContent: '--'
                    },
                    {
                        data: 'sku'
                    }
                ]
            });

            // Select all
            $('#select_all').on('change', function() {
                $('.product_checkbox').prop('checked', this.checked);
            });

            $('#import_selected').click(function(e) {
                e.preventDefault();
                let selected = $('input[name="selected_products[]"]:checked').map(function() {
                    return $(this).val();
                }).get();
                let category_id = $('#filter_category').val();
                if (!selected.length) return toastr.error('No products selected!');
                if (!category_id) return toastr.error('Please select a category!');

                $.post('{{ route('importExistingProduct.store') }}', {
                    _token: '{{ csrf_token() }}',
                    selected_products: selected,
                    category_id: category_id
                }, function(res) {
                    toastr.success(res.msg);
                    table.ajax.reload();
                }).fail(function(xhr) {
                    toastr.error(xhr.responseJSON.msg);
                });
            });
        });

        // Edit placeholder
        function editProduct(id) {
            alert("Edit product ID: " + id);
        }
    </script>
@endsection
