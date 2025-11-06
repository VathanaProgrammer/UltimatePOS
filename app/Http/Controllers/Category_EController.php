<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiModel\Categories_E;
use App\ApiModel\Catalog;

class Category_EController extends Controller
{
    //

    public function store(Request $request){

        $validatedData = $request->validate([
            "catalog_id" => "required|exists:catologs,id",
            "name" => "required|string|max:255",
            "description" => "nullable|string|max:225"
        ]);


        Categories_E::create($validatedData);

        $output = [
            'sucess' => true,
            'msg' => "Category added successfully"
        ];

        return redirect()->back()->with("status", $output);

    }

    public function index(){
        $categories = Categories_E::all();
        $catalogs = Catalog::all()->pluck('name', 'id');
        
        return view('E_Commerce.category.index', compact('categories', 'catalogs'));
    }
}
