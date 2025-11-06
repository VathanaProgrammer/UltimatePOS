@extends('layouts.app')
@section('title', 'Catalog Details')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
    <section class="content-header py-4">
        <header class="flex justify-between">
            <h1 class="text-3xl font-semibold text-gray-800">
                Categories</span>
            </h1>
        </header>


        <div class="shadow-md bg-white p-6 mt-6">
            <header class="w-full flex justify-end">

                <button type="button" class="px-4 py-2 text-md font-medium bg-blue-600 text-white rounded hover:bg-blue-700"
                    data-toggle="modal" data-target="#categoryModal">
                    <i class="fa fa-plus mr-2"></i> Add Category
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
                        @foreach ($categories as $c)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $c->name }}</td>
                                <td class="px-3 py-2">{{ $c->description ?? '--' }}</td>
                                <td class="px-3 py-2 flex gap-2">
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
        </div>
    </section>

    @include('E_Commerce.category.category_model')
@endsection
