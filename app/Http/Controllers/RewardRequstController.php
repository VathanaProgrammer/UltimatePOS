<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RewardRequstController extends Controller
{
    //
    public function index(){
        return view('E_Commerce.reward-request.index');
    }
}