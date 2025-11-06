@extends('layouts.app')
@section('title', __('E-Commerce Products'))
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Product</h1>

        <div class="filter-class shadow-md rounded-[5px] bg-white p-4 mt-4">
            <h2 class="text-xl font-medium text-gray-800 text-start mb-4">Filter</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Unit Dropdown -->
                <div class="form-group">
                    {!! Form::label('unit', 'Unit:') !!}
                    {!! Form::select(
                        'unit',
                        [
                            '' => 'Select Unit',
                            '1' => 'Piece',
                            '2' => 'Kg',
                            '3' => 'Liter',
                        ],
                        null,
                        ['class' => 'form-control'],
                    ) !!}
                </div>

                <!-- Is Active Dropdown -->
                <div class="form-group">
                    {!! Form::label('is_active', 'Is Active:') !!}
                    {!! Form::select(
                        'is_active',
                        [
                            '' => 'Select Status',
                            '1' => 'Active',
                            '0' => 'Inactive',
                        ],
                        null,
                        ['class' => 'form-control'],
                    ) !!}
                </div>

                <!-- Category Dropdown with Plus -->
                <div class="form-group">
                    {!! Form::label('category', 'Category:') !!}
                    <div class="flex items-center ">
                        {!! Form::select(
                            'category',
                            [
                                '' => 'Select Category',
                                'electronics' => 'Electronics',
                                'fashion' => 'Fashion',
                                'grocery' => 'Grocery',
                            ],
                            null,
                            ['class' => 'form-control flex-1'],
                        ) !!}
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" data-href="#"
                            title="@lang('category.add_category')" data-container=".view_modal">
                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Brand Dropdown with Plus -->
                <div class="form-group">
                    {!! Form::label('brand', 'Brand:') !!}
                    <div class="flex items-center">
                        {!! Form::select(
                            'brand',
                            [
                                '' => 'Select Brand',
                                'apple' => 'Apple',
                                'nike' => 'Nike',
                                'samsung' => 'Samsung',
                            ],
                            null,
                            ['class' => 'form-control flex-1'],
                        ) !!}
                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal" data-href="#"
                            title="@lang('brand.add_brand')" data-container=".view_modal">
                            <i class="fa fa-plus-circle text-primary fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <section class="shadow-md rounded-[5px] bg-white mt-4 p-4">
            <header class="flex justify-between">
                <h1 class="text-xl text-gray-700 font-semibold text-start">All Products</h1>
                <div class="flex gap-2">
                    <a href="{{ route('importExistingProduct.show')}}" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-md font-medium">
                        <i class="fa fa-download mr-2"></i> Use Existing Products
                    </a>
                </div>
            </header>

            <div class="w-full flex justify-end mt-6">
                <form class="flex items-center justify-end max-w-[300px] min-w[200px]">
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input type="text" id="simple-search"
                            class="border-2 bg-white border-gray-500 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-gray-700 block w-full ps-10 p-2.5 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search branch name..." required />
                    </div>
                </form>
            </div>

            <div class="relative overflow-x-auto mt-6">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-md text-medium text-gray-700 bg-gray-100">
                        <tr>
                            <th class="px-2 py-2">Product image</th>
                            <th class="px-2 py-2">Action</th>
                            <th class="px-2 py-2">Product</th>
                            <th class="px-2 py-2">Business Location</th>
                            <th class="px-2 py-2">Unit Purchase Price</th>
                            <th class="px-2 py-2">Unit Selling Price</th>
                            <th class="px-2 py-2">Current Stock</th>
                            <th class="px-2 py-2">Product Type</th>
                            <th class="px-2 py-2">Category</th>
                            <th class="px-2 py-2">Brand</th>
                            <th class="px-2 py-2">Tax</th>
                            <th class="px-2 py-2">SKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="border-b">
                                <td class="px-2 py-2">
                                    @php
                                        $image = $product->image
                                            ? asset('/uploads/img/' . $product->image)
                                            : asset('images/no-image.png');
                                    @endphp
                                    <img src="{{ $image }}" class="w-16 h-16 object-cover rounded-md"
                                        alt="{{ $product->name }}">
                                </td>

                                <td class="px-2 py-2">
                                    <button class="text-blue-600 hover:underline">Edit</button>
                                </td>

                                <td class="px-2 py-2">{{ $product->name }}</td>
                                <td class="px-2 py-2">{{ $product->business_location ?? 'N/A' }}</td>
                                <td class="px-2 py-2">${{ number_format($product->unit_purchase_price ?? 0, 2) }}</td>
                                <td class="px-2 py-2">${{ number_format($product->unit_selling_price ?? 0, 2) }}</td>
                                <td class="px-2 py-2">{{ $product->total_stock ?? 0 }}</td>
                                <td class="px-2 py-2">{{ ucfirst($product->type) }}</td>
                                <td class="px-2 py-2">{{ $product->category_name ?? 'N/A' }}</td>
                                <td class="px-2 py-2">{{ $product->brand_name ?? 'N/A' }}</td>
                                <td class="px-2 py-2">{{ $product->tax_name ?? '0%' }}</td>
                                <td class="px-2 py-2">{{ $product->sku }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </section>
    </section>

@endsection
