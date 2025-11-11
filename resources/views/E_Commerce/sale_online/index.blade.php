@extends('layouts.app')
@section('title', __('Sale Online List'))
<script src="https://cdn.tailwindcss.com"></script>
@section('content')

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
                        <tr >
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
           url: '{{ route("sale_online.data") }}',
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
       columns: [
           { data: 'action', name: 'action', orderable: false, searchable: false },
           { data: 'created_at', name: 'created_at' },
           { data: 'customer_name', name: 'customer_name' },
           { data: 'mobile', name: 'mobile' },
           { data: 'address', name: 'address' },
           { data: 'stauts', name: 'stauts' },
           { data: 'shipping_status', name: 'shipping_status' },
           { data: 'total_qty', name: 'total_qty' },
           { data: 'total', name: 'total' }
       ]
   });
}); // <-- This closes the document ready
</script>
@endsection

