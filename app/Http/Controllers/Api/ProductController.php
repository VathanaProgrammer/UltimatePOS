<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ProductE;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $category = $request->query('category'); // category name or ID
        $search = $request->query('search'); // search query


        $query = ProductE::with(['product.variations' => function ($q) {
            $q->select('id', 'product_id', 'default_sell_price', 'sell_price_inc_tax');
        }, 'product']);

        // Filter by category if provided
        if ($category && $category !== 'All') {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Filter by search query if provided
        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->get();

        $data = $products->map(function ($item) {
            $product = $item->product;

            $price = null;
            if ($product && $product->variations->isNotEmpty()) {
                $variation = $product->variations->first();
                $price = $variation->sell_price_inc_tax ?? $variation->default_sell_price;
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'is_active' => $item->is_active,
                'product' => [
                    'id' => $product->id ?? null,
                    'name' => $product->name ?? '',
                    'price' => $price,
                    'image_url' => isset($product->image) ? url('uploads/img/' . $product->image) : null,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}