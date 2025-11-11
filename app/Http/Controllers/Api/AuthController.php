<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ApiUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Contact;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Fake OTP
        $otp = rand(100000, 999999);

        // Find or create contact
        $contact = Contact::firstOrCreate(
            ['mobile' => $validatedData['phone']],
            [
                'name' => $validatedData['name'],
                'business_id' => 1,
                'type' => 'customer',
                'created_by' => 1,
            ]
        );

        // Create API user
        $user = ApiUser::firstOrCreate([
            'contact_id' => $contact->id
        ]);

        // Create JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP generated',
            'otp' => $otp
        ])->cookie('token', $token, 60, '/', null, false, true);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required|string',
        ]);

        $contact = Contact::where('mobile', $credentials['phone'])->first();

        if (!$contact) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = ApiUser::where('contact_id', $contact->id)->first();

        if (!$user) {
            // optionally create an API user if it doesn't exist
            $user = ApiUser::create(['contact_id' => $contact->id]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
        ])->cookie('token', $token, 60, '/', null, false, true);
    }

    // 🔹 Get user from JWT token
    public function user(Request $request)
    {
        try {
            $token = $request->cookie('token'); // get token from HttpOnly cookie
            $user = JWTAuth::setToken($token)->toUser();

            // Load the linked contact
            $user->load('contact');

            // Return both api_user info and contact info
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'profile_url' => $user->profile_url,
                    'name' => $user->contact->name ?? null,
                    'mobile' => $user->contact->mobile ?? null,
                ]
            ]);
        } catch (JWTException $e) {
            Log::error('JWT parse error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        // Always accept OTP for testing
        return response()->json([
            'success' => true,
            'message' => 'OTP verified',
        ]);
    }

    // 🔹 Logout
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()
                ->json(['message' => 'Logged out successfully', "sucess" => true])
                ->cookie('token', '', -1, '/'); // delete cookie

        } catch (JWTException $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to logout'], 500);
        }
    }
}
