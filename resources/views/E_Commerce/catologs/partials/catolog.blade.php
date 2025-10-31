@extends('layouts.app')
@section('title', 'Catalog Details')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">

        <header>
            <h1 class="text-3xl font-semibold text-gray-800">
                Catalog: <span class="text-blue-600">{{ $catolog->name ?? '--' }}</span>
            </h1>

            <button type="button" class="px-4 py-2 text-md font-medium bg-blue-600 text-white rounded hover:bg-blue-700"
                data-toggle="modal" data-target="#categoryModal">
                <i class="fa fa-plus mr-2"></i> Add Category
            </button>
        </header>

        <section class="mt-6 space-y-4">
            @forelse ($categories as $category)
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
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Price</th>
                                    <th class="px-4 py-3">Stock</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse ($category->products as $product)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-2 font-medium">{{ $product->name }}</td>
                                        <td class="px-4 py-2">${{ number_format($product->price, 2) }}</td>
                                        <td class="px-4 py-2">{{ $product->stock }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="{{ $product->status === 'active' ? 'text-green-600' : 'text-gray-500' }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 flex gap-2">
                                            <a href="#" class="text-blue-600 hover:underline">View</a>
                                            <a href="#" class="text-yellow-600 hover:underline">Edit</a>
                                            <a href="#" class="text-red-600 hover:underline">Delete</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No products in this
                                            category.</td>
                                    </tr>
                                @endforelse --}}
                            </tbody>
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
            <a href="" class="inline-block px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                ← Back to Catalog List
            </a>
        </div>
    </section>


    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
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
