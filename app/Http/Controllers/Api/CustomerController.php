<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ApiModel\Customer;
use App\ApiModel\Photo;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    //
    public function store(Request $request)
    {
        Log::info('Customer store request', ['data' => $request->all()]);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'photos.*' => 'required|image|max:2048', // validate multiple images
        ]);

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $filename = time() . '_' . $photo->getClientOriginalName();
                    $photo->move(public_path('c_photo'), $filename);

                    Photo::create([
                        'customer_id' => $customer->id,
                        'image_url' => $filename,
                    ]);
                }
            }

            return response()->json([
                'success' => 1,
                'msg' => 'Customer and photos saved successfully',
                'data' => $customer->load('photos')
            ]);
        } catch (\Exception $e) {
            Log::error('Customer store error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => 0,
                'msg' => 'Failed to save customer'
            ], 500);
        }
    }
}