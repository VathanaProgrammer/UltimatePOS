<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\ApiModel\Collector;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class CollectorAuthController extends Controller
{
    protected $cookieDomain = '.syspro.asia'; // production domain

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('Collector register request', ['data' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|unique:collector,username',
                'phone' => 'required|string|max:20|unique:collector,phone',
                'password' => 'required|string|min:3',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'msg' => $validator->errors()
                ], 422);
            }

            $collector = Collector::create([
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'role' => 'collector',
            ]);

            $token = JWTAuth::fromUser($collector);

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => 'Collector registered successfully',
                'data' => $collector
            ], 201)->cookie(
                'c_token',
                $token,
                60*24*7,       // 1 week
                '/',            // path
                '.syspro.asia',  // domain
                true,           // secure
                true,           // httpOnly
                false,          // raw
                'None'          // SameSite
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collector register error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => 0,
                'msg' => 'Registration failed'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('Collector login request', ['data' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|exists:collector,phone',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'msg' => $validator->errors()
                ], 422);
            }

            $collector = Collector::where('phone', $request->phone)->first();

            if (!$collector || !Hash::check($request->password, $collector->password)) {
                return response()->json([
                    'success' => 0,
                    'msg' => 'Invalid credentials'
                ], 401);
            }

            $token = JWTAuth::fromUser($collector);

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => 'Login successful',
                'data' => $collector
            ], 200)->cookie(
                'c_token',
                $token,
                60*24*7,
                '/',
                '.syspro.asia',
                true,
                true,
                false,
                'None'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collector login error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => 0,
                'msg' => 'Login failed'
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $token = $request->cookie('c_token');
            $collector = JWTAuth::setToken($token)->toUser();

            return response()->json([
                'success' => 1,
                'data' => $collector
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => 0,
                'msg' => 'Unauthenticated'
            ], 401);
        }
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json([
                'success' => 1,
                'msg' => 'Logged out successfully'
            ])->cookie(
                'c_token',
                '',
                -1,
                '/',
                '.syspro.asia',
                true,
                true,
                false,
                'None'
            );
        } catch (JWTException $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => 0,
                'msg' => 'Failed to logout'
            ], 500);
        }
    }
}