<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductOnlineController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');
        $brand = $request->input('brand');
        $status = $request->input('is_active');

        $products = DB::table('products_E as pe')
            ->join('products as p', 'pe.product_id', '=', 'p.id')
            ->leftJoin('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->select(
                'pe.id as id',               // for Blade $product->id
                'pe.is_active',
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
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
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                return $query->where('pe.is_active', $status);
            })
            ->groupBy(
                'pe.id',
                'pe.is_active',
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
                'c.name',
                'b.name'
            )
            ->get();

        return view('E_Commerce.products.index', compact('products'));
    }
}
