@extends('layouts.app')
@section('title', 'Catalogs')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Catalogs</h1>

        <section class="shadow-md rounded bg-white p-4 mt-4">
            <header class="flex justify-between items-center">
                <h2 class="text-xl text-gray-700 font-medium">All Catalogs</h2>
                <button type="button"  data-toggle="modal" data-target="#categoryModal"
                    class="px-4 py-2 text-md font-medium bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fa fa-plus mr-2"></i> Add Catalog
                </button>
            </header>

            <div class="relative overflow-x-auto mt-6">
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Desciption</th>
                            <th class="px-3 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($catologs as $c)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $c->name }}</td>
                                <td class="px-3 py-2">{{ $c->description }}</td>
                                <td class="px-3 py-2 flex gap-2">
                                    <a href="{{ route('catolog.show', ['id' => $c->id]) }}"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">View</a>
                                    <a href="#"
                                        class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">Edit</a>
                                    <a href="#"
                                        class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <div class="modal fade" id="CatalogModel" tabindex="-1" role="dialog" aria-labelledby="CatalogModelLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'category_e.store', 'method' => 'post']) !!}

                <!-- Hidden Catalog ID -->
                {!! Form::hidden('catalog_id', $catolog->id ?? null) !!}
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
                            {!! Form::textarea('description', null, [
                                'class' => 'form-control',
                                'rows' => 3,
                                'placeholder' => 'Description',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
                    <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                        data-dismiss="modal">@lang('messages.close')</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
