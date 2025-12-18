<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product; // make sure the namespace is correct
use Illuminate\Support\Facades\DB;

class ImportToRewardListController extends Controller
{
    // Show the page
    public function index()
    {
        return view('E_Commerce.import-to-reward-list.index'); // Blade handles the DataTable via AJAX
    }

    public function getData(Request $request)
    {
        $appUrl = env('APP_URL');
        $imagePath = $appUrl . '/uploads/img/';
        $empty_path = "/img/default.png";

        $products = Product::select(
            'products.id',
            'products.name',
            'products.sku',
            DB::raw("CASE
                        WHEN products.image IS NULL OR products.image =''
                        THEN '$empty_path'
                        ELSE CONCAT('$imagePath', products.image) END as image"), // prepend APP_URL
            DB::raw('categories.name as category'),
            DB::raw('business.name as business_name'),
            DB::raw('GROUP_CONCAT(DISTINCT business_locations.name SEPARATOR ", ") as locations')
        )
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('business', 'business.id', '=', 'products.business_id')
            ->leftJoin('product_locations', 'product_locations.product_id', '=', 'products.id')
            ->leftJoin('business_locations', 'business_locations.id', '=', 'product_locations.location_id')
            ->where('products.is_inactive', 0)
            ->where('products.not_for_selling', 0)
            ->whereNotIn('products.id', function ($query) {
                $query->select('product_id')->from('products_reward');
            })
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.image', 'categories.name', 'business.name')
            ->orderBy('products.name');

        return datatables()->of($products)->make(true);
    }



    // Import selected products to reward list
    public function import(Request $request)
    {
        $points = $request->input('points', []); // points[product_id] => value

        if (empty($points)) {
            return response()->json([
                'success' => false,
                'msg' => 'No products selected!'
            ]);
        }

        // Validate all points
        foreach ($points as $product_id => $points_required) {
            if (empty($points_required) || $points_required <= 0) {
                return response()->json([
                    'success' => false,
                    'msg' => "Points required must be greater than 0 for all products."
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $now = now();
            $data = [];

            foreach ($points as $product_id => $points_required) {
                $data[] = [
                    'product_id'      => $product_id,
                    'points_required' => $points_required,
                    'is_active'       => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now
                ];
            }

            DB::table('products_reward')->insert($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Products imported successfully with points!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg'     => 'Failed to import products.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}