@extends('layouts.app')
@section('title', __('E-Commerce Products'))
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Product</h1>

        <section class="shadow-md rounded-[5px] bg-white mt-4 p-4">
            <header class="flex justify-between">
                <h1 class="text-xl text-gray-700 font-semibold text-start">All Products</h1>
                <div class="flex gap-2">
                    <a href="{{ route('importExistingProduct.show') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-md font-medium">
                        <i class="fa fa-download mr-2"></i> Use Existing Products
                    </a>
                </div>
            </header>

            <div class="relative overflow-x-auto mt-6">
                <table id="products_table" class="table table-striped table-bordered w-full">
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
        columns: [
            { data: 'id', render: function(data) { return `<input type="checkbox" class="row_checkbox" value="${data}">`; }, orderable: false, searchable: false },
            { data: 'image', orderable: false, searchable: false },
            { 
                data: 'id',
                render: function(data, type, row) {
                    let currentStatus = parseInt(row.is_active) === 1 ? 'Active' : 'Inactive';
                    return `<div class="btn-group">
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Actions <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left">
                            <li><a href="#" onclick="removeProduct(${data}); return false;"><i class="fas fa-trash"></i> Remove</a></li>
                            <li class="dropdown-submenu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-toggle-on"></i> Update Status <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="updateStatus(${data}, 1); return false;">Active</a></li>
                                    <li><a href="#" onclick="updateStatus(${data}, 0); return false;">Inactive</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>`;
                },
                orderable: false,
                searchable: false
            },
            { data: 'name' },
            { data: 'business_location' },
            { data: 'unit_purchase_price',
                render: function(data){
                    return "$ " + data;
                }
             },
            { data: 'unit_selling_price',
                render: function(data){
                    return "$ "+ data;
                }
             },
            { data: 'total_stock' },
            { data: 'type' },
            { data: 'category_name' },
            { data: 'sku' },
            { 
                data: 'is_active',
                render: function(data, type, row) {
                    let isActive = parseInt(data) === 1;
                    let statusText = isActive ? 'Active' : 'Inactive';
                    let cls = isActive ? 'bg-green-600' : 'bg-red-600';
                    return `<span class="status-badge text-white px-2 py-1 rounded-md text-sm font-medium ${cls}">${statusText}</span>`;
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
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload();
            toastr.success(res.msg || 'Removed successfully');
        },
        error: function() {
            toastr.error('Something went wrong');
        }
    });
}

// Update Status via Dropdown
function updateStatus(id, status) {
    let statusText = status === 1 ? 'Active' : 'Inactive';
    if (!confirm(`Are you sure you want to change the status to "${statusText}"?`)) return;

    $.ajax({
        url: '/products/' + id + '/status',
        type: 'POST',
        data: { 
            is_active: status, 
            _token: '{{ csrf_token() }}' 
        },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload();
            toastr.success(res.msg || 'Status updated successfully');
        },
        error: function() {
            toastr.error('Failed to update status');
        }
    });
}
</script>

<style>
/* Optional: Better styling for submenu */
.dropdown-submenu {
    position: relative;
}
.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -1px;
}
</style>
@endsection