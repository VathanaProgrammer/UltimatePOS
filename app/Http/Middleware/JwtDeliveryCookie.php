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
            JWTAuth::setToken($token)->authenticate();
            auth()->shouldUse('api_delivery'); // use delivery guard
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token invalid: '.$e->getMessage()], 401);
        }

        return $next($request);
    }
}