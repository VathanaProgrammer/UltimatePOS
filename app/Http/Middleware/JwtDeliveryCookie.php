<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtDeliveryCookie
{
    public function handle($request, Closure $next)
    {
        $token = $request->cookie('delivery_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized (no token)'], 401);
        }

        try {
            // Set the guard for delivery
            auth()->shouldUse('api_delivery');

            // Authenticate the token
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized (user not found)'], 401);
            }


            // 🔥 Attach the authenticated user to Laravel Auth
            auth()->login($user);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalid'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token error: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}