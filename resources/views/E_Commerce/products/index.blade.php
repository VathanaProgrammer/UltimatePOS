@extends('layouts.app')
@section('title', __('E-Commerce Products'))
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
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
                    
                    // Create Bootstrap dropdown for status
                    return `<div class="dropdown">
                        <button class="status-badge cursor-pointer text-white px-2 py-1 rounded-md text-sm font-medium ${cls} dropdown-toggle" 
                                type="button" 
                                id="statusDropdown${row.id}" 
                                data-toggle="dropdown" 
                                aria-haspopup="true" 
                                aria-expanded="false">
                            ${statusText}
                        </button>
                        <div class="dropdown-menu" aria-labelledby="statusDropdown${row.id}">
                            <a class="dropdown-item" href="#" onclick="updateStatus(${row.id}, 1)">Active</a>
                            <a class="dropdown-item" href="#" onclick="updateStatus(${row.id}, 0)">Inactive</a>
                        </div>
                    </div>`;
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
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload();
            toastr.success(res.msg || 'Removed successfully');
        }
    });
}

// Update Status via Dropdown
function updateStatus(id, status) {
    $.ajax({
        url: '/products/' + id + '/status',
        type: 'POST',
        data: { 
            is_active: status, 
            _token: '{{ csrf_token() }}' 
        },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload();
            toastr.success(res.msg || 'Status updated');
        }
    });
}

// Keep the old editStatus function for backward compatibility (if needed elsewhere)
function editStatus(id) {
    let row = $('#products_table').DataTable().row($(`#products_table input[value="${id}"]`).parents('tr')).data();
    let currentStatus = row.is_active;

    let newStatus = prompt('Enter status: 1 for Active, 0 for Inactive', currentStatus);
    if (newStatus !== null && (newStatus === '0' || newStatus === '1')) {
        updateStatus(id, newStatus);
    }
}
</script>
@endsection