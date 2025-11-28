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
        try {
            $token = $request->cookie('c_token');

            if (!$token) {
                return response()->json(['message' => 'Unauthenticated. No token'], Response::HTTP_UNAUTHORIZED);
            }

            // Parse and authenticate the token directly using JWTAuth
            $user = JWTAuth::parseToken()->setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found'], Response::HTTP_UNAUTHORIZED);
            }

            // Attach the authenticated user to the request
            $request->attributes->set('user', $user);

        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error: ' . $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}