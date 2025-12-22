@extends('layouts.app')
@section('title', __('E-Commerce Products'))
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Product</h1>

        <section class="shadow-md rounded-[5px] bg-white mt-4 p-4">
            <header class="flex justify-between items-center">
                <h1 class="text-xl text-gray-700 font-semibold">All Products</h1>
                <div class="flex gap-2">
                    <a href="{{ route('importExistingProduct.show') }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-md font-medium">
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
            { 
                data: 'id', 
                render: function(data) { 
                    return `<input type="checkbox" class="row_checkbox" value="${data}">`; 
                }, 
                orderable: false, 
                searchable: false 
            },
            { data: 'image', orderable: false, searchable: false },
            { 
                data: 'id',
                name: 'action',
                render: function(data, type, row) {
                    let isActive = parseInt(row.is_active) === 1;
                    let currentText = isActive ? 'Active' : 'Inactive';

                    return `
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="removeProduct(${data}); return false;">
                                    <i class="fas fa-trash mr-1"></i> Remove
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Update Status</a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item ${isActive ? 'active' : ''}" href="#" onclick="updateStatus(${data}, 1); return false;">
                                            <i class="fas fa-check mr-1"></i> Active
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item ${!isActive ? 'active' : ''}" href="#" onclick="updateStatus(${data}, 0); return false;">
                                            <i class="fas fa-times mr-1"></i> Inactive
                                        </a>
                                    </li>
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
            { data: 'unit_purchase_price', render: data => '$ ' + data },
            { data: 'unit_selling_price', render: data => '$ ' + data },
            { data: 'total_stock' },
            { data: 'type' },
            { data: 'category_name' },
            { data: 'sku' },
            { 
                data: 'is_active',
                render: function(data) {
                    let isActive = parseInt(data) === 1;
                    let text = isActive ? 'Active' : 'Inactive';
                    let bg = isActive ? 'bg-green-600' : 'bg-red-600';
                    return `<span class="px-3 py-1 text-white text-sm font-medium rounded-full ${bg}">${text}</span>`;
                },
                orderable: false,
                searchable: false
            }
        ]
    });

    // Check/Uncheck all checkboxes
    $('#checkAll').on('click', function() {
        $('.row_checkbox').prop('checked', this.checked);
    });

    // Enable dropdown submenu hover/open (Bootstrap 5)
    $('.dropdown-submenu .dropdown-toggle').on('click', function(e) {
        $(this).next('.dropdown-menu').toggle();
        e.stopPropagation();
        e.preventDefault();
    });
});

// Remove Product
function removeProduct(id) {
    if (!confirm('Are you sure you want to remove this product?')) {
        return;
    }

    $.ajax({
        url: '/products/' + id + '/remove',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'DELETE'  // if your route expects DELETE
        },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload(null, false);
            toastr.success(res.msg || 'Product removed successfully');
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.msg || 'Failed to remove product');
        }
    });
}

// Update Status
function updateStatus(id, status) {
    let statusText = status === 1 ? 'Active' : 'Inactive';

    if (!confirm(`Change status to "${statusText}"?`)) {
        return;
    }

    $.ajax({
        url: '/products/' + id + '/status',
        type: 'POST',
        data: {
            is_active: status,
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload(null, false);
            toastr.success(res.msg || 'Status updated successfully');
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.msg || 'Failed to update status');
        }
    });
}
</script>

<style>
/* Submenu positioning */
.dropdown-submenu {
    position: relative;
}
.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -6px;
    border-radius: 0 6px 6px 6px;
}
</style>
@endsection