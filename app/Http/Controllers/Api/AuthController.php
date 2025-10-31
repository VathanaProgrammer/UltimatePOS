<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ApiUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // 🔹 Register new user
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:api_users,username',
            'phone'    => 'required|string|max:20|unique:api_users,phone',
            'profile_url'=> 'nullable|url|max:255',
        ]);

        $user = ApiUser::create($validatedData);

        // 🔹 Generate JWT token
        $token = JWTAuth::fromUser($user);

        Log::info('User registered', ['username' => $user->username, 'token' => $token]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ])->cookie(
            'token',
            $token,
            60,
            '/',
            null,
            false,
            true // HttpOnly
        );
    }

    // 🔹 Login using username + phone
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'phone'    => 'required|string',
        ]);

        Log::info('Login attempt', $credentials);

        try {
            $user = ApiUser::where('username', $credentials['username'])
                           ->where('phone', $credentials['phone'])
                           ->first();

            if (!$user) {
                Log::warning('Auth attempt failed', $credentials);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = JWTAuth::fromUser($user);

            Log::info('Token generated', ['token' => $token]);

            return response()->json([
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
            return response()->json($user);
        } catch (JWTException $e) {
            Log::error('JWT parse error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    }

    // 🔹 Logout
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()
                ->json(['message' => 'Logged out successfully'])
                ->cookie('token', '', -1, '/'); // delete cookie
        } catch (JWTException $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to logout'], 500);
        }
    }
}
