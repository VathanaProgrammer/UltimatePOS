<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportExistProductController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');
        $brand = $request->input('brand');
        $status = $request->input('status');

        $products = DB::table('products as p')
            // Join product locations
            ->leftJoin('product_locations as pl', 'pl.product_id', '=', 'p.id')
            ->leftJoin('business_locations as bl', 'bl.id', '=', 'pl.location_id')

            // Variations + stock details
            ->leftJoin('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')

            // Category + Brand
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')

            // Exclude products that already exist in products_E
            ->whereNotIn('p.id', function ($query) {
                $query->select('product_id')->from('products_E');
            })
            ->select(
                'p.id',
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
                'bl.location_id',
                'bl.landmark',
                'c.name as category_name',
                'b.name as brand_name',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as total_stock'),
                DB::raw('MIN(v.default_purchase_price) as unit_purchase_price'),
                DB::raw('MIN(v.sell_price_inc_tax) as unit_selling_price')
            )
            ->when($category, function ($query, $category) {
                return $query->where('p.category_id', $category);
            })
            ->when($brand, function ($query, $brand) {
                return $query->where('p.brand_id', $brand);
            })
            ->groupBy(
                'p.id',
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
                'c.name',
                'b.name'
            )
            ->get();


            $categories = DB::table('categories_E as c')
            ->pluck('name', 'id');

        return view('E_Commerce.products.import_exist_product', compact('products', "categories"));
    }

    public function importToEcommerce(Request $request)
    {
        // dd($request->all());
        $selected = $request->input('selected_products', []);

        if (empty($selected)) {
            return back()->with('error', 'No productEs selected!');                     
        }

        $request->validate([
            'category_id' => 'required|exists:categories_E,id',
        ]);

        // Get existing product IDs in products_E
        $existing = DB::table('products_E')->pluck('product_id')->toArray();

        // Filter only products not already in products_E
        $newProducts = array_diff($selected, $existing);

        if (empty($newProducts)) {
            return back()->with('error', 'All selected products already exist!');
        }

        $insertData = [];
        foreach ($newProducts as $productId) {
            $insertData[] = [
                'product_id' => $productId,
                'category_id' => $request->category_id,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        //dd($insertData);

        // Insert all at once for efficiency
       DB::transaction(function() use ($insertData) {
            DB::table('products_E')->insert($insertData);
        });

        return back()->with('success', count($insertData) . ' products added to e-commerce!');
    }

}
