@extends('layouts.app')
@section('title', __('Sale Online List'))
<script src="https://cdn.tailwindcss.com"></script>
@section('content')
    @include('E_Commerce.sale_online.model')

    <section class="content-header no-print">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Sale List Online</h1>
    </section>

    <section class="content no-print">

        <!-- Filters -->
        @component('components.filters', ['title' => __('Filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('location', __('Business Location') . ':') !!}
                    {!! Form::select('location', ['loc1' => 'Location 1', 'loc2' => 'Location 2'], null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => 'All',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('customer', __('Customer') . ':') !!}
                    {!! Form::select('customer', ['cust1' => 'Customer 1', 'cust2' => 'Customer 2'], null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => 'All',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status', __('Status') . ':') !!}
                    {!! Form::select('status', ['pending' => 'Pending', 'completed' => 'Completed'], null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => 'All',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('shipping_status', __('Shipping Status') . ':') !!}
                    {!! Form::select('shipping_status', ['pending' => 'Pending', 'shipped' => 'Shipped'], null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => 'All',
                    ]) !!}
                </div>
            </div>
        @endcomponent

        <!-- Table -->
        <section class="bg-white shadow-lg rounded-[10px] p-6">
            {{-- <header class="flex justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-700">All Sale Online</h2>
                {{-- <div class="input-group">
                    {!! Form::text('search_text', null, [
                        'class' => 'form-control',
                        'placeholder' => 'Search by Order No or Customer Name...',
                    ]) !!}
                </div>
            </header> --}}

            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="sale_online_table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th class="my-2">Status</th>
                            <th class="my-2">Shipping Status</th>
                            <th>Total Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </section>

    </section>

@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            let table = $('#sale_online_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('sale_online.data') }}',
                    data: function(d) {
                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.status = $('#so_list_filter_status').val();
                        d.shipping_status = $('#so_list_shipping_status').val();
                        if ($('#sell_list_filter_date_range').val()) {
                            let dr = $('#sell_list_filter_date_range').data('daterangepicker');
                            d.start_date = dr.startDate.format('YYYY-MM-DD');
                            d.end_date = dr.endDate.format('YYYY-MM-DD');
                        }
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'stauts',
                        name: 'stauts'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'total_qty',
                        name: 'total_qty'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    }
                ]
            });

            // Modal opening function
            function openOrderModal(data) {
                $('#orderModal').modal('show');

                $('#orderModal .modal-title').text('Order #' + (data.id || 'N/A'));
                $('#convertToSaleBtn').attr('data-order-id', data.id);
                $('#convertAndOpenSaleBtn').attr('data-order-id', data.id);

                $('#modalOrderNo').text('#' + (data.id || 'N/A'));
                $('#modalCustomerName').text(data.customer_name || 'N/A');
                $('#modalPhone').text(data.mobile || 'N/A');
                $('#modalAddress').text(data.address || 'N/A');
                $('#modalStatus').html(data.stauts || 'N/A');
                $('#modalDate').text(data.created_at || 'N/A');
                $('#modalTotal').text( (data.total || 0));

                let tbody = $('#modalProducts');
                tbody.html('');

                (data.details || []).forEach(d => {
                    tbody.append(`
                <tr>
                    <td>${d.name}</td>
                    <td>$${d.price}</td>
                    <td>${d.qty}</td>
                    <td>$${d.total_line}</td>
                    <td>
                        <img src="${d.image}" 
                            style="max-width: 80px; max-height: 80px; object-fit: contain; border: 2px solid white; border-radius: 8px;">
                    </td>
                </tr>
            `);
                });
            }

            // Row click
            $('#sale_online_table tbody').on('click', 'tr', function() {
                let data = table.row(this).data();
                if (!data) return;
                openOrderModal(data);
            });

            // Handle notification link query param
            const urlParams = new URLSearchParams(window.location.search);
            const orderIdToOpen = urlParams.get('open_order');

            if (orderIdToOpen) {
                // Wait until table draw completes
                $('#sale_online_table').on('draw.dt', function() {
                    let tableData = table.data().toArray();
                    let orderData = tableData.find(d => d.id == orderIdToOpen);
                    if (orderData) {
                        openOrderModal(orderData);
                    }
                });
            }

            // Convert buttons (unchanged)
            $('#convertToSaleBtn').on('click', function() {
                let order_id = $(this).data('order-id');
                if (!order_id) return alert('Order ID missing!');
                $.ajax({
                    url: '/convert-to-sale-order',
                    method: 'POST',
                    data: {
                        order_id: order_id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert('Converted successfully!');
                        $('#orderModal').modal('hide');
                        if (typeof table !== 'undefined') table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        alert('Something went wrong: ' + xhr.responseText);
                    }
                });
            });

            $('#convertAndOpenSaleBtn').on('click', function() {
                let order_id = $(this).data('order-id');
                if (!order_id) return alert('Order ID missing!');
                $.ajax({
                    url: '/convert-to-sale-order',
                    method: 'POST',
                    data: {
                        order_id: order_id,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        window.location.href =
                            '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}'
                    },
                    error: function(xhr) {
                        alert('Something went wrong: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
