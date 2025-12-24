<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductOnlineController extends Controller
{
    public function data(Request $request)
    {
        $path = '/uploads/img/';
        $empty_path = "/img/default.png";
        $query = DB::table('products_E as pe')
            ->join('products as p', 'pe.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('product_locations as pl', 'pl.product_id', '=', 'p.id')
            ->leftJoin('business_locations as bl', 'pl.location_id', '=', 'bl.id')
            ->leftJoin('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', function ($join) {
                $join->on('vld.variation_id', '=', 'v.id')
                    ->on('vld.location_id', '=', 'pl.location_id');
            })
            ->select(
                'pe.id',
                'pe.is_active',
                'p.name',
                DB::raw("
                        CASE 
                            WHEN p.image IS NULL OR p.image = '' 
                            THEN '$empty_path'
                            ELSE CONCAT('$path', p.image)
                        END as image
                    "),
                'p.sku',
                'p.type',
                'c.name as category_name',
                DB::raw("FORMAT(COALESCE(SUM(vld.qty_available), 0), 2) as total_stock"),
                DB::raw("FORMAT(MIN(v.default_purchase_price), 2) as unit_purchase_price"),
                DB::raw("FORMAT(MIN(v.sell_price_inc_tax), 2) as unit_selling_price"),
                DB::raw("GROUP_CONCAT(DISTINCT bl.name SEPARATOR ', ') as business_location")
            )
            ->groupBy(
                'pe.id',
                'pe.is_active',
                'p.id',
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
                'c.name'
            );

        return DataTables::of($query)
            // ADD CUSTOM FILTERS FOR EACH SEARCHABLE COLUMN
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('p.name', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('sku', function ($query, $keyword) {
                $query->where('p.sku', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('category_name', function ($query, $keyword) {
                $query->where('c.name', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('business_location', function ($query, $keyword) {
                // Use HAVING for GROUP_CONCAT columns
                $query->having('business_location', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('type', function ($query, $keyword) {
                $query->where('p.type', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('total_stock', function ($query, $keyword) {
                // Since total_stock is an aggregate, search is tricky
                // You might want to disable search on this column
            })
            ->filterColumn('unit_purchase_price', function ($query, $keyword) {
                // Since this is an aggregate, search is tricky
                // You might want to disable search on this column
            })
            ->filterColumn('unit_selling_price', function ($query, $keyword) {
                // Since this is an aggregate, search is tricky
                // You might want to disable search on this column
            })
            ->addColumn('image', function ($row) {
                $src = $row->image ? asset($row->image) : asset('img/default.png');
                return '<img src="' . $src . '" class="w-14 h-14 object-cover rounded">';
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group">
                        <button class="btn btn-xs btn-primary" onclick="editProduct(' . $row->id . ')">Edit</button>
                    </div>';
            })
            ->addColumn('status', function ($row) {
                return $row->is_active ? '<span class="text-green-600 font-semibold">Active</span>'
                    : '<span class="text-red-600 font-semibold">Inactive</span>';
            })
            ->rawColumns(['image', 'action', 'status'])
            ->make(true);
    }
    
    public function remove($id)
    {
        $deleted = DB::table('products_E')->where('id', $id)->delete();

        if ($deleted) {
            return response()->json(['msg' => 'Product removed'], 200);
        }

        return response()->json(['msg' => 'Product not found or not removed'], 404);
    }

    /**
     * Update is_active status (called via AJAX).
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $updated = DB::table('products_E')
            ->where('id', $id)
            ->update(['is_active' => $request->is_active]);

        if ($updated) {
            return response()->json(['msg' => 'Status updated'], 200);
        }

        return response()->json(['msg' => 'Product not found or status not changed'], 404);
    }

    public function index()
    {
        return view('E_Commerce.products.index'); 
    }
}