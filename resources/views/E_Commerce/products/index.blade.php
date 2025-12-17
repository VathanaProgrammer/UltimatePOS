@extends('layouts.app')
@section('title', __('E-Commerce Products'))
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Product</h1>

        <div class="filter-class shadow-md rounded-[5px] bg-white p-4 mt-4">
            <h2 class="text-xl font-medium text-gray-800 text-start mb-4">Filter</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group">
                    {!! Form::label('unit', 'Unit:') !!}
                    {!! Form::select('unit', ['' => 'Select Unit', '1' => 'Piece', '2' => 'Kg', '3' => 'Liter'], null, [
                        'class' => 'form-control',
                    ]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('is_active', 'Is Active:') !!}
                    {!! Form::select('is_active', ['' => 'Select Status', '1' => 'Active', '0' => 'Inactive'], null, [
                        'class' => 'form-control',
                    ]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('category', 'Category:') !!}
                    <div class="flex items-center">
                        {!! Form::select(
                            'category',
                            ['' => 'Select Category', 'electronics' => 'Electronics', 'fashion' => 'Fashion', 'grocery' => 'Grocery'],
                            null,
                            ['class' => 'form-control flex-1'],
                        ) !!}
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" data-href="#"
                            title="@lang('category.add_category')" data-container=".view_modal">
                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('brand', 'Brand:') !!}
                    <div class="flex items-center">
                        {!! Form::select(
                            'brand',
                            ['' => 'Select Brand', 'apple' => 'Apple', 'nike' => 'Nike', 'samsung' => 'Samsung'],
                            null,
                            ['class' => 'form-control flex-1'],
                        ) !!}
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" data-href="#"
                            title="@lang('brand.add_brand')" data-container=".view_modal">
                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="shadow-md rounded-[5px] bg-white mt-4 p-4">
            <header class="flex justify-between">
                <h1 class="text-xl text-gray-700 font-semibold text-start">All Products</h1>
                <div class="flex gap-2">
                    <a href="{{ route('importExistingProduct.show') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-md font-medium">
                        <i class="fa fa-download mr-2"></i> Use Existing Products
                    </a>
                </div>
            </header>

            <div class="relative overflow-x-auto mt-6">
                <table id="products_table" class="w-full border border-gray-200">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>Product image</th>
                            <th>Action</th>
                            <th>Product</th>
                            <th>Business Location</th>
                            <th>Unit Purchase Price</th>
                            <th>Unit Selling Price</th>
                            <th>Current Stock</th>
                            <th>Product Type</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('#products_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('product.online.data') }}",
                columns: [{
                        data: 'id',
                        render: function(data) {
                            return `<input type="checkbox" class="row_checkbox" value="${data}">`;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id',
                        render: function(data) {
                            return `<div class="btn-group">
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info dropdown-toggle" data-toggle="dropdown">Actions</button>
                        <ul class="dropdown-menu dropdown-menu-left">
                            <li><a href="/products/${data}/view"><i class="fas fa-eye"></i> View</a></li>
                            <li><a href="#" onclick="removeProduct(${data})"><i class="fas fa-trash"></i> Remove</a></li>
                            <li><a href="#" onclick="editStatus(${data})"><i class="fas fa-toggle-on"></i> Update Status</a></li>
                        </ul>
                    </div>`;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'business_location'
                    },
                    {
                        data: 'unit_purchase_price',
                        render: function(data) {
                            return "$ " + data;
                        }
                    },
                    {
                        data: 'unit_selling_price',
                        render: function(data) {
                            return "$ " + data;
                        }
                    },
                    {
                        data: 'total_stock'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'sku'
                    },
                    {
                        data: 'is_active',
                        render: function(data, type, row) {
                            let isActive = parseInt(data) === 1;
                            let statusText = isActive ? 'Active' : 'Inactive';
                            let cls = isActive ? 'bg-green-600' : 'bg-red-600';
                            return `<span class="status-badge cursor-pointer text-white px-2 py-1 rounded-md text-sm font-medium ${cls}" data-id="${row.id}" data-status="${isActive ? 1 : 0}">${statusText}</span>`;
                        },
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Check/Uncheck all
            $('#checkAll').on('click', function() {
                $('.row_checkbox').prop('checked', this.checked);
            });
        });

        // Remove Product
        function removeProduct(id) {
            if (!confirm('Are you sure to remove this product?')) return;
            $.ajax({
                url: '/products/' + id + '/remove',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#products_table').DataTable().ajax.reload();
                    toastr.success(res.msg || 'Removed successfully');
                }
            });
        }

        // Edit Status Modal
        function editStatus(id) {
            let row = $('#products_table').DataTable().row($(`#products_table input[value="${id}"]`).parents('tr')).data();
            let currentStatus = row.is_active;

            let newStatus = prompt('Enter status: 1 for Active, 0 for Inactive', currentStatus);
            if (newStatus !== null && (newStatus === '0' || newStatus === '1')) {
                $.ajax({
                    url: '/products/' + id + '/status',
                    type: 'POST',
                    data: {
                        is_active: newStatus,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        $('#products_table').DataTable().ajax.reload();
                        toastr.success(res.msg || 'Status updated');
                    }
                });
            }
        }
    </script>
    <style>
    table.dataTable tbody tr {
        height: 72px !important;
    }

    table.dataTable tbody td {
        padding: 16px 20px !important;
        line-height: 1.6 !important;
        vertical-align: middle !important;
    }
</style>

@endsection
