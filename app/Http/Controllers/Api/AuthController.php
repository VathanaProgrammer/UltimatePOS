<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ApiUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;
use App\ApiModel\Otp;

class AuthController extends Controller
{
// 🔹 Fake register
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        // Generate fake OTP
        $otpCode = rand(100000, 999999);

        // ✅ Correct: use Eloquent model that implements JWTSubject
        $user = ApiUser::create([
            'username' => $validatedData['username'],
            'phone' => $validatedData['phone'],
        ]);
        $token = JWTAuth::fromUser($user); // ✅ works

        return response()->json([
            'success' => true,
            'message' => 'OTP generated successfully',
            'otp' => $otpCode,
            'user' => $user
        ])->cookie(
            'token',     // cookie name
            $token,      // JWT token
            60,          // 60 minutes
            '/',         // path
            null,        // domain (default)
            false,       // secure (set true on HTTPS)
            true         // HttpOnly
        );
    }
    // 🔹 Login using username + phone
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'nullable|string',
            'phone'    => 'required|string',
        ]);

        Log::info('Login attempt', $credentials);

        try {
            $user = ApiUser::where('phone', $credentials['phone'])
                        //    ->where('username', $credentials['username'])
                           ->first();

            if (!$user) {
                Log::warning('Auth attempt failed', $credentials);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = JWTAuth::fromUser($user);

            Log::info('Token generated', ['token' => $token]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
            ])->cookie(
                'token',
                $token,
                60,
                '/',
                null,
                false,
                true // HttpOnly
            );

        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Login error'], 500);
        }
    }

    // 🔹 Get user from JWT token
    public function user(Request $request)
    {
        try {
            $token = $request->cookie('token'); // get token from HttpOnly cookie
            $user = JWTAuth::setToken($token)->toUser();
            return response()->json(['success' => true , 'user' => $user]);
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
