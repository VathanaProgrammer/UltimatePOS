<?php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JwtCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        // 1️⃣ Get the token from the cookie
        $token = $request->cookie('c_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated. No token'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // 2️⃣ Pass the token string to authenticate
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found'], Response::HTTP_UNAUTHORIZED);
            }

            // 3️⃣ Attach the user to the request
            $request->attributes->set('user', $user);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token error: ' . $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}