<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiModel\Catalog;

class CatologController extends Controller
{
    //
    public function index()
    {
        $catologs = Catalog::all();
        return view('E_Commerce.catologs.index', compact('catologs'));
    }

    public function show($id)
    {

    }

    public function showByCategory($id)
    {
        // Get catalog with nested relationships
        $catalog = Catalog::with('categories.product_e.product')->findOrFail($id);

        // Now you can access everything
        // Example: $catalog->categories[0]->product_e[0]->product
        return view('E_Commerce.catologs.partials.catolog', compact('catalog'));
    }

    public function store(Request $request)
    {
        

        try {
            // Prepare input
            $input = $request->only(['name', 'description']);
            $input['created_by'] = $request->user()->id;

            // Validate
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Create catalog
            $catalog = Catalog::create($input);

            // Return success with structured output
            $output = [
                'success' => true,
                'data' => $catalog,
                'msg' => __('Catalog created successfully!'),
            ];

            return redirect()->back()->with('status', $output);

        } catch (\Exception $e) {
            \Log::error('Error creating catalog: ' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('Error creating catalog: ') . $e->getMessage(),
            ];

            return redirect()->back()->with('status', $output)->withInput();
        }
    }
}
