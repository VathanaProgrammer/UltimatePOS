<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ApiModel\Categories_E;

class CategoryController extends Controller
{
    //
    public function all()
    {
        $categories = Categories_E::select('id', 'name')->get();

        return response()->json([
            "success" => true,
            "data" => $categories
        ]);
    }

}
