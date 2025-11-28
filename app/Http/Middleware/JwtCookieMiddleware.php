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
        $token = $request->cookie('c_token'); // get token from cookie

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated. No token'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // For recent versions, you must use JWTAuth::setToken($token)->authenticate()
            // If setToken does not exist, use JWTAuth::authenticate($token)
            $user = JWTAuth::authenticate($token);

            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found'], Response::HTTP_UNAUTHORIZED);
            }

            $request->attributes->set('user', $user);

        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error: ' . $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}