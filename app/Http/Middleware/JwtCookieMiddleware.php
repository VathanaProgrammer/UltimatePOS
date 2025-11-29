<?php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        // Support multiple cookie names
        $tokenNames = ['c_token', 'token'];
        $token = null;

        foreach ($tokenNames as $name) {
            if ($request->cookie($name)) {
                $token = $request->cookie($name);
                break;
            }
        }

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated. No token'], 401);
        }

        try {
            // Authenticate user using the token
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found'], 401);
            }

            $request->merge(['user' => $user]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}