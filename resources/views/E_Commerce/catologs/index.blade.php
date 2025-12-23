@extends('layouts.app')
@section('title', 'Catalogs')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<section class="content-header py-4">
    <h1 class="text-3xl font-semibold text-gray-800">Catalogs</h1>

    <section class="shadow-md rounded bg-white p-4 mt-4">
        <header class="flex justify-between items-center">
            <h2 class="text-xl text-gray-700 font-medium">All Catalogs</h2>
            <button type="button" data-toggle="modal" data-target="#CatalogModel"
                class="px-4 py-2 text-md font-medium bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fa fa-plus mr-2"></i> Add Catalog
            </button>
        </header>

        <div class="relative overflow-x-auto mt-6">
            <table id="catalogs_table" class="w-full text-sm text-left text-gray-700">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Description</th>
                        <th class="px-3 py-2">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</section>

<div class="modal fade" id="CatalogModel" tabindex="-1" role="dialog" aria-labelledby="CatalogModelLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => 'catolog.store', 'method' => 'post']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Add Catalog</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {!! Form::label('name', 'Name:*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Catalog Name']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('description', 'Description:') !!}
                    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Description']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Save</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">Close</button>
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
<script>
$(document).ready(function() {
    var table = $('#catalogs_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('catalogs.data') }}",
        columns: [
            { data: 'name' },
            { data: 'description' },
            {
                data: 'id',
                render: function(data) {
                    return `<div class="btn-group">
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info dropdown-toggle" data-toggle="dropdown">
                            Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left">
                            <li><a href="#" onclick="editCatalog(${data})"><i class="fas fa-edit"></i> Edit</a></li>
                            <li><a href="#" onclick="deleteCatalog(${data})"><i class="fas fa-trash"></i> Delete</a></li>
                        </ul>
                    </div>`;
                },
                orderable: false,
                searchable: false
            }
        ]
    });
});

// Edit Catalog placeholder
function editCatalog(id) {
    alert('Edit catalog ID: ' + id);
}

// Delete Catalog with confirmation
function deleteCatalog(id) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! All products under this catalog will be deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    $.ajax({
        url: `/catalogs/${id}/delete`,
        type: "DELETE",
        data: { _token: "{{ csrf_token() }}" },
        success: function() {
            toastr.success("Catalog deleted successfully.");
            $('#catalogs_table').DataTable().ajax.reload();
        },
        error: function() {
            toastr.error("Failed to delete catalog.");
        }
    });
}
</script>
@endsection
