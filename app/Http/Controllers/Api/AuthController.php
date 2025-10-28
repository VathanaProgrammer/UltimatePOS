<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ApiUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // 🔹 Register new user
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:api_users',
            'password'   => 'required|string|min:3|confirmed',
            'phone'      => 'nullable|string|max:20',
            'profile_url'=> 'nullable|url|max:255',
        ]);

        // 🔹 Use bcrypt for JWT
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = ApiUser::create($validatedData);

        // 🔹 Generate JWT token
        $token = JWTAuth::fromUser($user);

        Log::info('User registered', ['email' => $user->email, 'token' => $token]);

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

public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|string|email',
        'password' => 'required|string',
    ]);

    Log::info('Login attempt', $credentials);

    try {
        if (!$token = auth('api')->attempt($credentials)) {
            Log::warning('Auth attempt failed', $credentials);
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

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
            true, // HttpOnly
            'None'
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
