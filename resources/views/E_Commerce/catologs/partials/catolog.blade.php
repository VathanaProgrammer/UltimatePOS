@extends('layouts.app')
@section('title', 'Catalog Details')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<section class="content-header py-4">

    <header class="flex justify-between">
        <h1 class="text-3xl font-semibold text-gray-800">
            Catalog: <span class="text-blue-600">{{ $catalog->name ?? '--' }}</span>
        </h1>
        <div>
            <button type="button" class="px-4 py-2 text-md font-medium bg-blue-600 text-white rounded hover:bg-blue-700"
                data-toggle="modal" data-target="#categoryModal">
                <i class="fa fa-plus mr-2"></i> Add Category
            </button>
            <a href="{{ route('importExistingProduct.show') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-md font-medium">
                <i class="fa fa-download mr-2"></i> Use Existing Products
            </a>
        </div>
    </header>

    <section class="mt-6 space-y-4">
        @forelse ($catalog->categories as $category)
            <div x-data="{ open: false }" class="border border-gray-200 shadow-sm rounded-xl bg-white overflow-hidden">
                <!-- Category Header -->
                <div @click="open = !open"
                    class="flex justify-between items-center px-5 py-3 bg-gray-50 border-b cursor-pointer">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ $category->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $category->description ?? 'No description' }}</p>
                    </div>
                    <div>
                        <span x-show="!open" class="text-gray-400 text-lg font-bold">+</span>
                        <span x-show="open" class="text-gray-400 text-lg font-bold">-</span>
                    </div>
                </div>

                <!-- Products Table -->
                <div x-show="open" x-transition class="overflow-x-auto px-5 py-3">
                    <table id="category_table_{{ $category->id }}" class="table table-striped table-bordered w-full">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-10">
                No categories found under this catalog.
            </div>
        @endforelse
    </section>

    <!-- Back Button -->
    <div class="mt-8">
        <a href="{{ route('catologs.index') }}" class="inline-block px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
            ← Back to Catalog List
        </a>
    </div>
</section>

<!-- Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => 'category_e.store', 'method' => 'post']) !!}
            {!! Form::hidden('catalog_id', $catalog->id ?? null) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('Add Category')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-sm-12">
                        {!! Form::label('name', 'Category Name:*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Catalog Name']) !!}
                    </div>
                    <div class="form-group col-sm-12">
                        {!! Form::label('description', 'Description:') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Description']) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.26.17/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.26.17/dist/sweetalert2.min.css
" rel="stylesheet">
<script>
document.addEventListener("DOMContentLoaded", function() {
    @foreach ($catalog->categories as $category)
        $('#category_table_{{ $category->id }}').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('catalog.products.data', ['category' => $category->id]) }}",
            columns: [
                { data: 'name' },
                { data: 'price' },
                { data: 'stock' },
                { 
                    data: 'status',
                    render: function(data, type, row) {
                        let cls = data === 'active' ? 'text-green-600' : 'text-gray-500';
                        return `<span class="${cls}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    },
                    orderable: false,
                    searchable: false
                },
                { 
                    data: 'id',
                    render: function(data) {
                        return `
                            <div class="flex gap-2">
                                <a href="#" onclick="editProduct(${data})" class="text-yellow-600 hover:underline">Edit</a>
                                <a href="#" onclick="deleteProduct(${data})" class="text-red-600 hover:underline">Delete</a>
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                }
            ]
        });
    @endforeach
});

// Example AJAX handlers
function editProduct(id) {
    alert('Edit product ' + id); // Replace with modal or redirect
}

function deleteProduct(id) {
    if(!confirm('Are you sure to delete this product?')) return;
    $.ajax({
        url: '/products/' + id + '/delete',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            @foreach ($catalog->categories as $category)
                $('#category_table_{{ $category->id }}').DataTable().ajax.reload();
            @endforeach
            toastr.success(res.msg || 'Deleted successfully');
        }
    });
}
</script>
@endsection
