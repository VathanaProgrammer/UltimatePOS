@extends('layouts.app')
@section('title', 'Product Reward List')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')

    <!-- Content Header -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            Product Reward List
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
                These products are eligible for reward points.
            </small>
        </h1>
    </section>

    <section class="content">

        <!-- Filters -->
        @component('components.filters_custom', ['title' => __('Filters')])
            <div class="col-md-3">
                {!! Form::label('search_text', __('Search') . ':') !!}
                {!! Form::text('search_text', null, [
                    'class' => 'form-control',
                    'id' => 'search_text',
                    'placeholder' => 'Product name, SKU, category...',
                ]) !!}
            </div>
        @endcomponent

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Reward Products</h3>
            </div>

            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="reward_products_table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>Image</th>
                                <th>Action</th>
                                <th>Product Name</th>
                                <th>Point Reward</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>SKU</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </section>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="statusForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Update Product Status</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="status_product_id">
                        <div class="form-group">
                            <label for="status_select">Status</label>
                            <select name="is_active" id="status_select" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Status</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

@section('javascript')
    <script>
        $(document).ready(function() {

            var table = $('#reward_products_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('product.reward.data') }}",
                columns: [{
                        data: 'id',
                        render: function(data) {
                            return `<input type="checkbox" class="product_checkbox" value="${data}">`;
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
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info dropdown-toggle" data-toggle="dropdown">
                            Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left">
                            <li><a href="/products/${data}/view"><i class="fas fa-eye"></i> View</a></li>
                            <li><a href="/products/${data}/edit"><i class="fas fa-edit"></i> Edit</a></li>
                            <li><a href="#" onclick="removeReward(${data})"><i class="fas fa-trash"></i> Remove</a></li>
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
                        data: 'points_required',
                        defaultContent: 0
                    },
                    {
                        data: 'locations',
                        defaultContent: '--'
                    },
                    {
                        data: 'category',
                        defaultContent: '--'
                    },
                    {
                        data: 'sku'
                    },
                    {
                        data: 'is_active',
                        render: function(data, type, row) {

                            // Ensure proper boolean interpretation
                            let isActive = parseInt(data) === 1;

                            let statusText = isActive ? 'Active' : 'Inactive';
                            let cls = isActive ? 'bg-green-600' : 'bg-red-600';

                            return `<span class="status-badge cursor-pointer text-white px-2 py-1 rounded-md text-sm font-medium ${cls}" 
                 data-id="${row.id}" data-status="${isActive ? 1 : 0}">
                ${statusText}
            </span>`;
                        },
                        orderable: false,
                        searchable: false
                    }

                ]
            });

            // Check all
            $('#checkAll').on('click', function() {
                $('.product_checkbox').prop('checked', this.checked);
            });

            // Open status modal
            function openStatusModal(id, currentStatus) {
                $('#status_product_id').val(id);
                $('#status_select').val(currentStatus);
                $('#statusModal').modal('show');
            }

            // Click badge to edit
            $('#reward_products_table').on('click', '.status-badge', function() {
                let id = $(this).data('id');
                let currentStatus = $(this).data('status');
                openStatusModal(id, currentStatus);
            });

            // Click edit status from dropdown
            window.editStatus = function(id) {
                let rowData = $('#reward_products_table').DataTable().row($(
                    '#reward_products_table input[value="' + id + '"]').parents('tr')).data();
                let currentStatus = rowData.is_active;
                openStatusModal(id, currentStatus);
            }

            // Save status from modal
            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                let data = $(this).serialize();

                $.ajax({
                    url: "{{ route('product.reward.status.update') }}",
                    type: "POST",
                    data: data + '&_token={{ csrf_token() }}',
                    success: function(res) {
                        $('#statusModal').modal('hide');
                        $('#reward_products_table').DataTable().ajax.reload();
                        toastr.success(res.msg);
                    },
                    error: function(res) {
                        toastr.error(res.responseJSON?.msg || "Failed to update status");
                    }
                });
            });

        });

        // Remove product
        function removeReward(id) {
            if (!confirm("Remove this product from reward list?")) return;

            $.ajax({
                url: "/product-reward/" + id + "/remove",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    toastr.success("Removed from reward list.");
                    $('#reward_products_table').DataTable().ajax.reload();
                },
                error: function() {
                    toastr.error("Failed to remove.");
                }
            });
        }
    </script>
@endsection
