<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;

class ApiRewardController extends Controller
{
    public function getData(Request $request)
    {
        // Path prefix for images
        $imagePath = '';

        // Only select active reward products
        $products = Product::select(
            'products.id',
            'products.name',
            'products.image',
            'products_reward.points_required as reward_points',
            'products_reward.is_active'
        )
            ->join('products_reward', 'products_reward.product_id', '=', 'products.id')
            ->where('products_reward.is_active', 1)
            ->orderBy('products.name')
            ->get()
            ->map(function ($item) use ($imagePath) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image_url' => $item->image
                        ? asset('uploads/img/' . $item->image)
                        : asset('img/default.png'),
                    'reward_points' => $item->reward_points,
                    'is_active' => $item->is_active,
                ];
            });

        return response()->json([
            "success" => true,
            "data" => $products
        ]);
    }
}