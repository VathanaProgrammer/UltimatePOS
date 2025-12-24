@extends('layouts.app')
@section('title', 'Reward Products Import')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    @include('E_Commerce.import-to-reward-list.model')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            Reward Products Import
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
                Select products to add to reward list
            </small>
        </h1>
    </section>

    <section class="content">
        <!-- Filters -->
        <!-- @component('components.filters_custom', ['title' => __('Filters')])
            <div class="col-md-3">
                {!! Form::label('search_text', __('Search') . ':') !!}
                {!! Form::text('search_text', null, [
                    'class' => 'form-control',
                    'id' => 'search_text',
                    'placeholder' => 'Product name, SKU, category...',
                ]) !!}
            </div>
        @endcomponent -->

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Products</h3>
                <button id="import_selected" class="tw-btn bg-blue-600 tw-text-white px-4 py-2 float-end rounded-md">
                    <i class="fa fa-download mr-2"></i> Import Selected
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="products_table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>Image</th>
                                <th>Action</th>
                                <th>Product Name</th>
                                <th>Business / Location</th>
                                <th>Category</th>
                                <th>SKU</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>

@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            var table = $('#products_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('products_reward.index') }}',
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
                        render: function(data, type, row) {
                            return data ?
                                `<img src="${data}" width="50" height="50" alt="${row.name}">` :
                                '--';
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id',
                        render: function(data) {
                            return `<div class="btn-group">
                                <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle" data-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left">
                                    <li><a href="/products/${data}/view"><i class="fas fa-eye"></i> View</a></li>
                                    <li><a href="/products/${data}/edit"><i class="fas fa-edit"></i> Edit</a></li>
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
                        data: 'business_name',
                        defaultContent: '--'
                    },
                    {
                        data: 'category',
                        defaultContent: '--'
                    },
                    {
                        data: 'sku'
                    }
                ]
            });

            // Check all
            $('#checkAll').on('click', function() {
                $('.product_checkbox').prop('checked', this.checked);
            });

            // Import selected -> open modal
            // Import selected -> open modal
            $('#import_selected').on('click', function() {
                var selected = [];
                $('.product_checkbox:checked').each(function() {
                    var row = $(this).closest('tr');
                    var name = row.find('td:eq(3)').text(); // Product name
                    var sku = row.find('td:eq(6)').text(); // SKU column
                    selected.push({
                        id: $(this).val(),
                        name: name,
                        sku: sku
                    });
                });

                if (selected.length === 0) {
                    toastr.error('Please select at least one product!');
                    return;
                }

                // Create inputs in modal
                var html = '';
                selected.forEach(function(prod) {
                    html += `<div class="form-group mb-2">
                    <label>${prod.name} (SKU: ${prod.sku}) - Points Required</label>
                    <input type="number" class="form-control points_input" name="points[${prod.id}]" min="1" required>
                 </div>`;
                });
                $('#pointsContainer').html(html);
                $('#pointsModal').modal('show');
            });

            // Save points
            $('#savePointsBtn').on('click', function() {
                var valid = true;
                $('.points_input').each(function() {
                    if ($(this).val() === '' || $(this).val() <= 0) {
                        valid = false;
                        $(this).addClass('border-red-500'); // highlight
                    } else {
                        $(this).removeClass('border-red-500');
                    }
                });

                if (!valid) {
                    toastr.error('Please enter points for all selected products!');
                    return;
                }

                var data = $('#pointsForm').serialize();
                $.ajax({
                    url: '{{ route('products_reward.import') }}',
                    type: 'POST',
                    data: data + '&_token={{ csrf_token() }}',
                    success: function(res) {
                        toastr.success(res.msg || 'Products imported successfully!');
                        table.ajax.reload();
                        $('#pointsModal').modal('hide');
                    },
                    error: function(res) {
                        toastr.error(res.msg || 'Failed to import products!');
                    }
                });
            });

        });
    </script>
@endsection
