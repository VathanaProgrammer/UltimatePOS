<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;
use Carbon\Carbon;

class DeliveryAuthController extends Controller
{
    // 🔹 Register a delivery user
    public function register(Request $request)
    {
        try {
            Log::info('Delivery register request', ['data' => $request->all()]);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'first_name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
            ]);

            // Assign "delivery" role
            $user->assignRole('delivery');

            $token = JWTAuth::fromUser($user);

            Log::info('Delivery user created', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Delivery user registered',
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ])->cookie(
                'delivery_token',
                $token,
                60,
                '/',
                '.syspro.asia',
                true,
                true,
                false,
                'None'
            );
        } catch (\Exception $e) {
            Log::error('Delivery register error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 🔹 Delivery login
    public function login(Request $request)
    {
        try {
            Log::info('Delivery login request', ['data' => $request->all()]);

            $credentials = $request->validate([
                'email' => 'required|string|exists:users,username',
                'password' => 'required|string',
            ]);

            if (!$token = auth('api_delivery')->attempt([
                'username' =>  $credentials['email'],
                'password' => $credentials['password'],
            ])) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Invalid_email_or_password'
                ], 401);
            }

            $user = auth('api_delivery')->user();
            
            $expectedRole = 'Delivery#' . $user->business_id;
            if (!$user->hasRole($expectedRole)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Unauthorized'
                ], 403);
            }

            $name = $user?->first_name ?? "Delivery";
            if(!empty($user->surname || $user->surname == '')){
                $name = $user->surname . ' ' . $user->first_name ?? 'Delivery';
            }
            $now = Carbon::now()->format('Y-m-d H:i:s A');

            $raw = "$name just Logined\n" .
                "Time: $now";

            TelegramService::sendRawMessage(-5084064052, $raw);

            return response()->json([
                'success' => true,
                'msg' => 'Login successful',
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ])->cookie(
                'delivery_token',
                $token,
                60 * 24 * 30,
                '/',
                '.syspro.asia',
                true,
                true,
                false,
                'None'
            );
        } catch (\Exception $e) {
            Log::error('Delivery login error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'msg' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    // 🔹 Get delivery user profile
    public function profile(Request $request)
    {
        try {
            $token = $request->cookie('delivery_token');

            if (!$token) {
                return response()->json(['success' => false, 'msg' => 'Unauthenticated'], 401);
            }

            $user = JWTAuth::setToken($token)->toUser();

            if (!$user->hasRole('delivery')) {
                return response()->json(['success' => false, 'msg' => 'Unauthorized'], 403);
            }

            return response()->json([
                'success' => true,
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ]);
        } catch (JWTException $e) {
            Log::error('Delivery profile JWT error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => 'Unauthenticated: ' . $e->getMessage()], 401);
        } catch (\Exception $e) {
            Log::error('Delivery profile error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // 🔹 Logout delivery
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            Log::info('Delivery logout initiated', ['token' => $token ? 'exists' : 'missing']);

            JWTAuth::invalidate($token);
            Log::info('JWT invalidated successfully');

            return response()
                ->json(['msg' => 'Logged out successfully', 'success' => true])
                ->cookie('delivery_token', '', -1, '/', '.syspro.asia');
        } catch (JWTException $e) {
            Log::error('Delivery logout JWT error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => 'Failed to logout: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Delivery logout error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}