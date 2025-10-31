@extends('layouts.app')
@section('title', 'Catalogs')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<section class="content-header py-4">
    <h1 class="text-3xl font-semibold text-gray-800">Create Catolog</h1>
    <section class="shadow-md rounded bg-white p-4 mt-4">
        <form action="{{ route('catologs.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Catalog Name:</label>
                <input type="text" id="name" name="name" class="w-full border border-gray-300 rounded px-3 py-2"
                    placeholder="Enter catalog name" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description:</label>
                <textarea id="description" name="description" rows="4"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                    placeholder="Enter catalog description"></textarea>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Catalog</button>
        </form>
</section>