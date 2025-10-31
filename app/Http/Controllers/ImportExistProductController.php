<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportExistProductController extends Controller
{
    //
        public function importToEcommerce(Request $request)
    {
        $selected = $request->input('selected_products', []); // array of selected IDs

        if(empty($selected)) {
            return back()->with('error', 'No products selected!');
        }

        // Example: Insert selected products into your products_E table
        foreach($selected as $productId) {
            DB::table('products_E')->insert([
                'product_id' => $productId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', count($selected).' products added to sale!');
    }
}
