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
                render: function (data, type, row) {
                    let isActive = parseInt(row.is_active) === 1;

                    return `
                    <div class="relative inline-block text-left">
                        <button 
                            onclick="toggleDropdown(event)"
                            class="inline-flex justify-center items-center px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none">
                            Actions
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="dropdown-menu hidden absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                            <a href="#" 
                            onclick="removeProduct(${data}); closeDropdowns(); return false;"
                            class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                🗑 Remove
                            </a>

                            <div class="border-t my-1"></div>

                            <a href="#"
                            onclick="updateStatus(${data}, 1); closeDropdowns(); return false;"
                            class="block px-4 py-2 text-sm hover:bg-gray-100 ${isActive ? 'font-semibold text-green-600' : ''}">
                                ✔ Active
                            </a>

                            <a href="#"
                            onclick="updateStatus(${data}, 0); closeDropdowns(); return false;"
                            class="block px-4 py-2 text-sm hover:bg-gray-100 ${!isActive ? 'font-semibold text-red-600' : ''}">
                                ✖ Inactive
                            </a>
                        </div>
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

    // Check/Uncheck all
    $('#checkAll').on('click', function() {
        $('.row_checkbox').prop('checked', this.checked);
    });
});

function toggleDropdown(e) {
    e.stopPropagation();

    // Close other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.add('hidden');
    });

    const menu = e.currentTarget.nextElementSibling;
    menu.classList.toggle('hidden');
}

function closeDropdowns() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', closeDropdowns);

// Remove Product
function removeProduct(id) {
    if (!confirm('Are you sure you want to remove this product?')) return;

    $.ajax({
        url: '/products/' + id + '/remove',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload(null, false);
            toastr.success(res.msg || 'Product removed');
        },
        error: function() {
            toastr.error('Failed to remove product');
        }
    });
}

// Update Status
function updateStatus(id, status) {
    let text = status === 1 ? 'Active' : 'Inactive';
    if (!confirm(`Change status to "${text}"?`)) return;

    $.ajax({
        url: '/products/' + id + '/status',
        type: 'POST',
        data: { is_active: status, _token: '{{ csrf_token() }}' },
        success: function(res) {
            $('#products_table').DataTable().ajax.reload(null, false);
            toastr.success(res.msg || 'Status updated');
        },
        error: function() {
            toastr.error('Failed to update status');
        }
    });
}
</script>

<style>
.dropdown-menu { min-width: 140px; }
.toast-success {
    background-color: #51a351 !important;
}

.toast-error {
    background-color: #bd362f !important;
}

.toast-info {
    background-color: #2f96b4 !important;
}

.toast-warning {
    background-color: #f89406 !important;
}
</style>
@endsection