@extends('layouts.app')
@section('title', __('Import Existing E-Commerce Products'))
<script src="https://cdn.tailwindcss.com"></script>

@section('content')

    <section class="content-header py-4">
        <h1 class="text-3xl font-semibold text-gray-800">Import Products</h1>

        <form class="w-full min-h-[400px] rounded bg-white mt-4 p-4 rounded-md shadow-md"
            action="{{ route('importExistingProduct.store') }}" method="POST">
            <header class="flex mb-4 gap-4 items-end justify-between">
                <div class="form-group min-w-[300px]">
                    {!! Form::label('Category', 'Select Category:*', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) !!}
                    {!! Form::select('category_id', $categories, null, [
                        'class' => 'form-control select2',
                        'placeholder' => 'Choose a branch',
                        'required',
                    ]) !!}
                    @error('barcode_type')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-2 items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white min-w-[130px] rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-md font-medium">
                        <i class="fa fa-download mr-2"></i> Import
                    </button>

                    <!-- ✅ Replaced <form> with <div> -->
                    <div class="flex items-center max-w-[300px] min-w-[200px]">
                        <label for="simple-search" class="sr-only">Search</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" id="simple-search"
                                class="border-2 bg-white border-gray-500 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-gray-700 block w-full ps-10 p-2.5"
                                placeholder="Search branch name..." />
                        </div>
                    </div>
                </div>
            </header>

            @csrf
            <div class="overflow-y-auto "> {{-- {{ route('ecommerce.products.import_exist_product') --}}
                <table class="w-full text-left">
                    <thead class="bg-gray-100  sticky top-0">
                        <tr>
                            <th class="px-2 py-2"><input type="checkbox" id="select_all"></th>
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
                    <tbody id="product_table">
                        @foreach ($products as $product)
                            <tr class="border-b">
                                <td class="px-2 py-2">
                                    <input type="checkbox" name="selected_products[]" value="{{ $product->id }}">
                                </td>
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
                                <td class="px-2 py-2">{{ $product->landmark . '(' . $product->location_id . ')' ?? 'N/A' }}
                                </td>
                                <td class="px-2 py-2">${{ number_format($product->unit_purchase_price ?? 0, 2) }}</td>
                                <td class="px-2 py-2">${{ number_format($product->unit_selling_price ?? 0, 2) }}</td>
                                <td class="px-2 py-2">{{ $product->total_stock ?? 0 }}</td>
                                <td class="px-2 py-2">{{ ucfirst($product->type) }}</td>
                                <td class="px-2 py-2">{{ $product->category_name ?? '--' }}</td>
                                <td class="px-2 py-2">{{ $product->brand_name ?? '--' }}</td>
                                <td class="px-2 py-2">{{ $product->tax_name ?? '0%' }}</td>
                                <td class="px-2 py-2">{{ $product->sku }}</td>
                            </tr>
                        @endforeach
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </form>

    </section>

    <script>
        const selectAll = document.getElementById('select_all');
        const tableRows = document.querySelectorAll('#product_table tr');

        selectAll.addEventListener('change', function() {
            tableRows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = this.checked;
            });
        });
    </script>

@endsection
