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
            ->leftJoin('product_locations as pl', 'pl.product_id', '=', 'p.id')
            ->leftJoin('business_locations as bl', 'bl.id', '=', 'pl.location_id')
            ->leftJoin('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->whereNotIn('p.id', function($q){
                $q->select('product_id')->from('products_E');
            })
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                DB::raw("IF(p.image IS NOT NULL, CONCAT('$imagePath', p.image), null) as image"),
                DB::raw('c.name as category_name'),
                DB::raw('b.name as brand_name'),
                DB::raw('COALESCE(SUM(vld.qty_available),0) as total_stock'),
                DB::raw('MIN(v.default_purchase_price) as unit_purchase_price'),
                DB::raw('MIN(v.sell_price_inc_tax) as unit_selling_price'),
                DB::raw('bl.landmark as business_location')
            )
            ->groupBy('p.id', 'p.name', 'p.sku', 'p.image', 'c.name', 'b.name', 'bl.landmark');

        return DataTables::of($products)
            ->addColumn('checkbox', function($row){
                return '<input type="checkbox" class="product_checkbox" value="'.$row->id.'">';
            })
            ->editColumn('image', function($row){
                return $row->image ? '<img src="'.$row->image.'" class="w-16 h-16 object-cover rounded-md">' : '--';
            })
            ->addColumn('action', function($row){
                return '<div class="btn-group">
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle" data-toggle="dropdown">
                        Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left">
                        <li><a href="#" onclick="editProduct('.$row->id.')"><i class="fas fa-edit"></i> Edit</a></li>
                    </ul>
                </div>';
            })
            ->rawColumns(['checkbox', 'image', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
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
            'msg' => count($insertData).' products imported successfully!'
        ]);
    }
}