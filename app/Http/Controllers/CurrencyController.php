<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CurrencyController extends Controller
{
    //
    public function index()
    {
        return view('settings.currency_settings');
    }

    public function data(Request $request)
    {
        $query = DB::table('currencies');

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                // Colored dropdown button
                $html = '<div class="btn-group">
    <button type="button" class="btn btn-info btn-sm mb-2 dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu dropdown-menu-left" role="menu">
        <li>
            <a href="javascript:void(0)" class="edit-btn dropdown-item text-warning"
                data-id="' . $row->id . '"
                data-country="' . $row->country . '"
                data-currency="' . $row->currency . '"
                data-code="' . $row->code . '"
                data-symbol="' . $row->symbol . '"
                data-thousand="' . $row->thousand_separator . '"
                data-decimal="' . $row->decimal_separator . '"
                data-exchange_rate="' . $row->exchange_rate . '"
                data-toggle="modal" data-target="#currencyModal">
                <i class="fas fa-edit"></i> Edit
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" class="delete-btn dropdown-item text-danger"
                data-id="' . $row->id . '"
                data-toggle="modal" data-target="#deleteModal">
                <i class="fas fa-trash"></i> Delete
            </a>
        </li>
    </ul>
</div>';


                return $html;
            })
            ->rawColumns(['action'])
            ->make(true);
    }



    public function update(Request $request, $id)
    {
        try {

            // Validation
            $validated = $request->validate([
                'country'             => 'required|string|max:255',
                'currency'            => 'required|string|max:255',
                'exchange_rate'       => 'required|numeric|min:0',
                'code'                => 'required|string|max:10',
                'symbol'              => 'required|string|max:10',
                'thousand_separator'  => 'required|string|max:5',
                'decimal_separator'   => 'required|string|max:5',
            ]);

            // Update with DB
            DB::table('currencies')
                ->where('id', $id)
                ->update([
                    'country'             => $validated['country'],
                    'currency'            => $validated['currency'],
                    'exchange_rate'       => $validated['exchange_rate'],
                    'code'                => $validated['code'],
                    'symbol'              => $validated['symbol'],
                    'thousand_separator'  => $validated['thousand_separator'],
                    'decimal_separator'   => $validated['decimal_separator'],
                    'updated_at'          => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Currency updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            // return validation error to UI
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            DB::table('currencies')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Currency deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete currency',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}