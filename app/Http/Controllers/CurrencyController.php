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
                return '
        <button class="edit-btn px-2 py-1 bg-yellow-500 text-white rounded"
            data-id="' . $row->id . '"
            data-country="' . $row->country . '"
            data-currency="' . $row->currency . '"
            data-code="' . $row->code . '"
            data-symbol="' . $row->symbol . '"
            data-thousand="' . $row->thousand_separator . '"
            data-decimal="' . $row->decimal_separator . '"
            data-exchange_rate="'.$row->exchange_rate.'"
            data-toggle="modal" data-target="#currencyModal">
            Edit
        </button>

        <button class="delete-btn px-2 py-1 bg-red-600 text-white rounded"
            data-id="' . $row->id . '"
            data-toggle="modal" data-target="#deleteModal">
            Delete
        </button>
    ';
            })

            ->rawColumns(['action'])
            ->make(true);
    }
}