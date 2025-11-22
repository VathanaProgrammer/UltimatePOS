<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ImportExistProductController extends Controller
{
    public function index()
    {
        $categories = DB::table('categories_E')->pluck('name', 'id');
        return view('E_Commerce.products.import_exist_product', compact('categories'));
    }

    public function data(Request $request)
    {
        $appUrl = env('APP_URL');
        $imagePath = $appUrl . '/uploads/img/';

        $products = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->leftJoin('product_locations as pl', 'pl.product_id', '=', 'p.id') // ONLY product_locations
            ->leftJoin('business_locations as bl', 'bl.id', '=', 'pl.location_id')
            ->whereNotIn('p.id', function ($q) {
                $q->select('product_id')->from('products_E');
            })
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                DB::raw("IF(p.image IS NOT NULL, CONCAT('$imagePath', p.image), null) as image"),
                DB::raw('c.name as category_name'),
                DB::raw('b.name as brand_name'),
                DB::raw("FORMAT(0, 2) as total_stock"), // stock from variations ignored
                DB::raw("FORMAT(0, 2) as unit_purchase_price"), // purchase price ignored
                DB::raw("FORMAT(0, 2) as unit_selling_price"), // selling price ignored
                DB::raw("IFNULL(GROUP_CONCAT(DISTINCT bl.name SEPARATOR ', '), '-') as business_location")
            )
            ->groupBy('p.id', 'p.name', 'p.sku', 'p.image', 'c.name', 'b.name');

        return DataTables::of($products)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" name="selected_products[]" class="product_checkbox" value="' . $row->id . '">';
            })
            ->editColumn('image', function ($row) {
                return $row->image ? '<img src="' . $row->image . '" class="w-16 h-16 rounded-md object-cover">' : '--';
            })
            ->addColumn('action', function ($row) {
                return '<button onclick="editProduct(' . $row->id . ')" 
                class="px-2 py-1 bg-gray-200 rounded">Edit</button>';
            })
            ->rawColumns(['checkbox', 'image', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // MUST BE THIS — correct key name
        $selected = $request->input('selected_products', []);
        $category_id = $request->input('category_id');

        if (empty($selected)) {
            return response()->json(['success' => false, 'msg' => 'No products selected!'], 422);
        }

        $request->validate([
            'category_id' => 'required|exists:categories_E,id',
        ]);

        $existing = DB::table('products_E')->pluck('product_id')->toArray();
        $newProducts = array_diff($selected, $existing);

        if (empty($newProducts)) {
            return response()->json(['success' => false, 'msg' => 'All selected products already exist!'], 422);
        }

        $insertData = array_map(fn($id) => [
            'product_id' => $id,
            'category_id' => $category_id,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $newProducts);

        DB::table('products_E')->insert($insertData);

        return response()->json([
            'success' => true,
            'msg' => count($insertData) . ' products imported successfully!'
        ]);
    }
}