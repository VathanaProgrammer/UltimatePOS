<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use Illuminate\Support\Facades\DB;

class ProductRewardController extends Controller
{
    public function index()
    {
        return view('E_Commerce.product-reward.index');
    }

    public function getData(Request $request)
    {
        $appUrl = env('APP_URL');
        $imagePath = asset('uploads/img/');
    $defaultImage = asset('img/default.png');

        $products = Product::select(
            'products.id',
            'products.name',
            'products.sku',
            DB::raw("
                CASE 
                    WHEN products.image IS NULL OR products.image = '' 
                    THEN '$defaultImage'
                    ELSE CONCAT('$imagePath/', products.image)
                END as image
            "),
            DB::raw('categories.name as category'),
            DB::raw('business.name as business_name'),
            'products_reward.is_active',
            'products_reward.points_required',
            DB::raw("(SELECT GROUP_CONCAT(bl.name SEPARATOR ', ')
                 FROM product_locations pl
                 JOIN business_locations bl ON bl.id = pl.location_id
                 WHERE pl.product_id = products.id) as locations")
        )
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('business', 'business.id', '=', 'products.business_id')
            ->join('products_reward', 'products_reward.product_id', '=', 'products.id') // <--- this fixes the issue
            ->orderBy('products.name');

        return datatables()->of($products)
            ->editColumn('image', fn($row) => '<img src="' . $row->image . '" style="height:45px;width:45px;object-fit:cover;border-radius:6px;">')
            ->editColumn('locations', fn($row) => $row->locations ?: '--')
            ->rawColumns(['image', 'is_active'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_reward,product_id',
            'is_active'  => 'required|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            DB::table('products_reward')
                ->where('product_id', $request->product_id)
                ->update(['is_active' => $request->is_active]);

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Product status updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'Failed to update product status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}