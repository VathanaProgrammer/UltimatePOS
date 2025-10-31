<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ProductE;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all()
    {
        $products = ProductE::with(['product.variations' => function ($query) {
            $query->select('id', 'product_id', 'default_sell_price', 'sell_price_inc_tax');
        }])->get();

        $data = $products->map(function ($item) {
            $product = $item->product;

            $price = null;
            if ($product && $product->variations->isNotEmpty()) {
                $variation = $product->variations->first();
                $price = $variation->sell_price_inc_tax ?? $variation->default_sell_price;
            }

            // 🔥 Build final data that fits your TypeScript interface
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'is_active' => $item->is_active,
                'product' => [
                    'id' => $product->id ?? null,
                    'name' => $product->name ?? '',
                    'price' => $price,
                    'image_url' => isset($product->image) 
                        ? url('uploads/img/' . $product->image)
                        : null,
                    // you can include other attributes dynamically here
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    
}
