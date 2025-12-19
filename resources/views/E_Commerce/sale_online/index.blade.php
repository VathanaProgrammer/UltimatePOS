@extends('layouts.app')
@section('title', __('Sale Online List'))
@section('content')
@include('E_Commerce.sale_online.model')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Sale List Online</h1>
</section>

<section class="content no-print">

    <!-- Filters -->
    @component('components.filters_custom', ['title' => __('Filters')])
    {{-- Date Range --}}
    <div class="col-md-3">
        {!! Form::label('sell_list_filter_date_range', __('Date Range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, [
        'class' => 'form-control',
        'readonly',
        'id' => 'sell_list_filter_date_range',
        'placeholder' => __('Select date'),
        ]) !!}
    </div>

    {{-- Customer --}}
    <div class="col-md-3">
        {!! Form::label('sell_list_filter_customer_id', __('Customer') . ':') !!}
        {!! Form::select('sell_list_filter_customer_id', $customers, null, [
        'class' => 'form-control select2',
        'placeholder' => __('All'),
        'id' => 'sell_list_filter_customer_id',
        ]) !!}
    </div>

    {{-- Order Status --}}
    <div class="col-md-3">
        {!! Form::label('so_list_filter_status', __('Order Status') . ':') !!}
        {!! Form::select(
        'so_list_filter_status',
        [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        ],
        null,
        [
        'class' => 'form-control select2',
        'placeholder' => __('All'),
        'id' => 'so_list_filter_status',
        ],
        ) !!}
    </div>

    {{-- Shipping Status --}}
    <!-- <div class="col-md-3">
                {!! Form::label('so_list_shipping_status', __('Shipping Status') . ':') !!}
                {!! Form::select(
                    'so_list_shipping_status',
                    [
                        'pending' => 'Pending',
                        'preparing' => 'Preparing',
                        'packed' => 'Packed',
                        'shipped' => 'Shipped',
                        'out_for_delivery' => 'Out for delivery',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                    ],
                    null,
                    [
                        'class' => 'form-control select2',
                        'placeholder' => __('All'),
                        'id' => 'so_list_shipping_status',
                    ],
                ) !!}
            </div> -->

    {{-- Search --}}
    <div class="col-md-3 mt-4">
        {!! Form::label('search_text', __('Search') . ':') !!}
        {!! Form::text('search_text', null, [
        'class' => 'form-control',
        'id' => 'search_text',
        'placeholder' => 'Order ID, customer name, phone...',
        ]) !!}
    </div>
    @endcomponent

    <!-- Table -->
    <section class="bg-white shadow-lg rounded-[10px] p-6 mt-4">
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sale_online_table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Status</th>
                        <!-- <th>Shipping Status</th> -->
                        <th>Total Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

</section>


@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {

        // Init Select2
        $('.select2').select2({
            width: '100%',
            allowClear: true
        });

        // Date Range Picker
        $('#sell_list_filter_date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#sell_list_filter_date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                'YYYY-MM-DD'));
            table.ajax.reload();
        });

        $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.ajax.reload();
        });

        // DataTable
        let table = $('#sale_online_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('sale_online.data') }}',
                data: function(d) {
                    d.customer_id = $('#sell_list_filter_customer_id').val();
                    d.status = $('#so_list_filter_status').val();
                    d.shipping_status = $('#so_list_shipping_status').val();
                    d.search_text = $('#search_text').val();
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
                    data: 'total_qty',
                    name: 'total_qty'
                },
                {
                    data: 'total',
                    name: 'total'
                }
            ]
        });

        // Filters trigger reload
        $('#sell_list_filter_customer_id, #so_list_filter_status, #so_list_shipping_status').change(function() {
            table.ajax.reload();
        });

        $('#search_text').keyup(function() {
            table.ajax.reload();
        });

        // Row click modal
        $('#sale_online_table tbody').on('click', 'tr', function() {
            let data = table.row(this).data();
            if (!data) return;
            openOrderModal(data);
        });

        function openOrderModal(data) {
            $('#orderModal').modal('show');
            $('#orderModal .modal-title').text('Order #' + (data.id || 'N/A'));
            $('#convertToSaleBtn, #convertAndOpenSaleBtn').attr('data-order-id', data.id);

            if (data.is_converted == 1 || data.is_converted === 1 || data.is_converted === true) {
                $('#convertToSaleBtn').hide();
                $('#convertAndOpenSaleBtn').hide();
                // Optional: Show a message that it's already converted
                $('#conversionStatus').html('<span class="text-success">✓ Already converted</span>').show();
            } else {
                $('#convertToSaleBtn').show();
                $('#convertAndOpenSaleBtn').show();
                $('#conversionStatus').hide();
            }

            $('#modalOrderNo').text('#' + (data.id || 'N/A'));
            $('#modalCustomerName').text(data.customer_name || 'N/A');
            $('#modalPhone').text(data.mobile || 'N/A');
            $('#modalAddress').text(data.address || 'N/A');
            $('#modalStatus').html(data.stauts || 'N/A');
            $('#modalDate').text(data.created_at || 'N/A');
            $('#modalTotal').text((data.total || 0));

            let tbody = $('#modalProducts');
            tbody.html('');
            (data.details || []).forEach(d => {
                tbody.append(`
                <tr>
                    <td>${d.name}</td>
                    <td>$${d.price}</td>
                    <td>${d.qty}</td>
                    <td>$${d.total_line}</td>
                    <td><img src="${d.image}" style="max-width:80px; max-height:80px; object-fit:contain; border:2px solid white; border-radius:8px;"></td>
                </tr>
            `);
            });
        }

        // Convert only
        $('#convertToSaleBtn').click(function() {
            let order_id = $(this).data('order-id');

            $.ajax({
                url: "{{ route('sale_online.convert') }}",
                method: "POST",
                data: {
                    order_id: order_id,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    toastr.info("Converting order...");
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success("Order has been successfully converted!");
                        $('#orderModal').modal('hide');
                        $('#sale_online_table').DataTable().ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    toastr.error("Conversion failed");
                }
            });
        });

        // Convert and open Sell List
        $('#convertAndOpenSaleBtn').click(function() {
            let order_id = $(this).data('order-id');

            $.ajax({
                url: "{{ route('sale_online.convert') }}",
                method: "POST",
                data: {
                    order_id: order_id,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    toastr.info("Converting order...");
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success("Converted! Opening sell list...");
                        window.location.href = "/sells";
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    toastr.error("Conversion failed");
                }
            });
        });
    });
</script>
@endsection
