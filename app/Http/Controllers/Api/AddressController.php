<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ApiModel\ApiUserAddress as Address;

class AddressController extends Controller
{
    /**
     * Store a new saved address
     */
    public function store(Request $request)
    {
        try {
            // Validate only the required fields
            $data = $request->validate([
                'api_user_id' => 'required|numeric|exists:api_users,id',
                'label' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'details' => 'nullable|string|max:1000',
                'coordinates' => 'required|array',
                'coordinates.lat' => 'required|numeric',
                'coordinates.lng' => 'required|numeric',
            ]);

            $address = Address::create([
                'api_user_id' => $data['api_user_id'],
                'label' => $data['label'],
                'phone' => $data['phone'] ?? null,
                'details' => $data['details'] ?? null,
                'coordinates' => $data['coordinates'],
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully',
                'data' => $address
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show all saved addresses
     */
    public function show()
    {
        try {
            $addresses = Address::all();

            return response()->json([
                'success' => true,
                'message' => $addresses->isEmpty() ? 'No saved addresses found' : 'Saved addresses retrieved successfully',
                'data' => $addresses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve addresses: ' . $e->getMessage()
            ], 500);
        }
    }
}
