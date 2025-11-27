<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ApiModel\Customer;
use App\ApiModel\Photo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        // Log incoming request
        Log::info('Customer store request', ['data' => $request->all()]);

        try {
            // Validate input
            $request->validate([
                'name' => 'nullable|max:255',
                'phone' => 'nullable|max:20',
                'photos.*' => 'required|mimes:jpeg,jpg,png,gif,webp,heic|max:2048',
            ]);
        } catch (ValidationException $ve) {
            Log::warning('Validation failed', [
                'errors' => $ve->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => 0,
                'msg' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        }

        // Wrap database operations in transaction
        DB::beginTransaction();

        try {
            // Create customer
            $customer = Customer::create([
                'name' => $request->name ?? '',
                'phone' => $request->phone ?? '',
            ]);

            Log::info('Customer created', ['customer_id' => $customer->id]);

            // Save photos if provided
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if (!$photo->isValid()) {
                        Log::warning('Invalid uploaded file', ['file' => $photo]);
                        continue;
                    }

                    $filename = time() . '_' . $photo->getClientOriginalName();
                    $destination = public_path('c_photo');

                    try {
                        $photo->move($destination, $filename);
                    } catch (\Exception $moveEx) {
                        Log::error('Failed to move photo', [
                            'file' => $photo,
                            'error' => $moveEx->getMessage()
                        ]);
                        continue;
                    }

                    Photo::create([
                        'customer_id' => $customer->id,
                        'image_url' => $filename,
                    ]);

                    Log::info('Photo saved', ['filename' => $filename]);
                }
            } else {
                Log::info('No photos uploaded');
            }

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => 'Customer and photos saved successfully',
                'data' => $customer->load('photos')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Customer store error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => 0,
                'msg' => 'Failed to save customer'
            ], 500);
        }
    }
}