<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductOnlineController extends Controller
{
    public function data(Request $request)
    {
        $query = DB::table('products_E as pe')
            ->join('products as p', 'pe.product_id', '=', 'p.id')
            ->leftJoin('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('business_locations as bl', 'vld.location_id', '=', 'bl.id')
            ->select(
                'pe.id',
                'pe.is_active', // <-- add this field
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
                'c.name as category_name',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as total_stock'),
                DB::raw('MIN(v.default_purchase_price) as unit_purchase_price'),
                DB::raw('MIN(v.sell_price_inc_tax) as unit_selling_price'),
                DB::raw("GROUP_CONCAT(DISTINCT bl.name SEPARATOR ', ') as business_location")
            )
            ->groupBy(
                'pe.id',
                'pe.is_active', // <-- add this to group by
                'p.name',
                'p.image',
                'p.sku',
                'p.type',
                'c.name'
            );

        return DataTables::of($query)
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

    public function index()
    {
        return view('E_Commerce.products.index'); // Only loads the page — NO DATA
    }
}