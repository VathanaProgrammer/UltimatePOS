<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RewardRequstController extends Controller
{
    //
    public function index()
    {
        return view('E_Commerce.reward-request.index');
    }

    // RewardController.php
    public function adjustmentsData(Request $request)
    {
        $customerId = $request->input('contact_id');

        $query = DB::table('reward_point_histories')
            ->leftJoin('users', 'reward_point_histories.add_by', '=', 'users.id')
            ->leftJoin('contacts', 'reward_point_histories.contact_id', '=', 'contacts.id') // join contacts table
            ->select(
                'contacts.contact_id', // get contact_id from contacts table
                'reward_point_histories.points',
                'reward_point_histories.description',
                DB::raw("CONCAT(users.first_name, IF(users.last_name IS NULL OR users.last_name = '', '', CONCAT(' ', users.last_name))) as added_by"),
                'reward_point_histories.created_at'
            )
            ->where('reward_point_histories.type', 'adjustment')
            ->orderBy('reward_point_histories.created_at', 'desc');


        if ($customerId) {
            $query->where('reward_point_histories.contact_id', $customerId);
        }

        $query->orderBy('reward_point_histories.created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn() // adds DT_RowIndex automatically
            ->make(true);
    }
}