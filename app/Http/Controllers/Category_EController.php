<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiModel\Categories_E;
use App\ApiModel\Catalog;
use DataTables;

class Category_EController extends Controller
{
    public function index()
    {
        $catalogs = Catalog::all()->pluck('name', 'id');
        return view('E_Commerce.category.index', compact('catalogs'));
    }
    public function data()
    {
        $can_edit = true;   // set permission logic here
        $can_delete = true; // set permission logic here
        $category_type = 'E'; // example type

        $categories = Categories_E::with('catologs')->select('categories_E.*');

        return Datatables::of($categories)
            ->addColumn('catalog_name', fn($row) => $row->catologs->name ?? '--')
            ->addColumn('action', function ($row) use ($can_edit, $can_delete, $category_type) {
                $html = '';

                if ($can_edit) {
                    $html .= '<button type="button"
                            class="edit-btn tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary"
                            data-id="' . $row->id . '"
                            data-name="' . $row->name . '"
                            data-description="' . $row->description . '"
                            data-catalog_id="' . $row->catalog_id . '"
                        >
                        <i class="glyphicon glyphicon-edit"></i> Edit
                    </button>';
                }

                if ($can_delete) {
                    $html .= '&nbsp;<form method="POST" action="' . route('categories.destroy', $row->id) . '" style="display:inline;" onsubmit="return confirm(\'Are you sure?\');">'
                        . csrf_field() . method_field('DELETE') .
                        '<button type="submit" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error">
                            <i class="glyphicon glyphicon-trash"></i> Delete
                        </button>
                    </form>';
                }

                return $html;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'catalog_id' => 'required|exists:catologs,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:225'
        ]);

        Categories_E::create($validated);

        return response()->json(['success' => true, 'msg' => 'Category added successfully']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'catalog_id' => 'required|exists:catalogs,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:225'
        ]);

        $category = Categories_E::findOrFail($id);
        $category->update($validated);

        return response()->json(['success' => true, 'msg' => 'Category updated successfully']);
    }

    public function destroy($id)
    {
        Categories_E::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }
}