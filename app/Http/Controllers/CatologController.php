<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiModel\Catalog;
use DataTables;

class CatologController extends Controller
{
    // Show the main page (Blade loads DataTables)
    public function index()
    {
        return view('E_Commerce.catologs.index');
    }

    // Provide JSON data for AJAX DataTables
    public function data(Request $request)
    {
        $catalogs = Catalog::select(['id', 'name', 'description']);

        return DataTables::of($catalogs)
            ->addColumn('action', function ($catalog) {
                return '
                    <div class="flex gap-2">
                        <a href="/catalogs/'.$catalog->id.'" class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">View</a>
                        <a href="#" onclick="editCatalog('.$catalog->id.')" class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">Edit</a>
                        <a href="#" onclick="deleteCatalog('.$catalog->id.')" class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</a>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $catalog = Catalog::with('categories.product_e.product')->findOrFail($id);
        return view('E_Commerce.catologs.partials.catolog', compact('catalog'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $catalog = Catalog::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => $request->user()->id,
            ]);

            return redirect()->back()->with('status', [
                'success' => true,
                'msg' => __('Catalog created successfully!'),
                'data' => $catalog
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating catalog: ' . $e->getMessage());

            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => __('Error creating catalog: ') . $e->getMessage()
            ])->withInput();
        }
    }

    // Optional: add delete and edit handlers for AJAX
    public function destroy($id)
    {
        try {
            $catalog = Catalog::findOrFail($id);
            $catalog->delete();

            return response()->json(['success' => true, 'msg' => 'Catalog deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Error deleting catalog: '.$e->getMessage()]);
        }
    }
}