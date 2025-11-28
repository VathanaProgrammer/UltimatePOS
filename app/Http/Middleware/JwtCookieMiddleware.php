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
            $token = $request->cookie('c_token'); // read the cookie

            if (!$token) {
                return response()->json(['message' => 'Unauthenticated. No token'], Response::HTTP_UNAUTHORIZED);
            }

            $user = JWTAuth::setToken($token)->authenticate(); // tell JWTAuth to use this token

            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found'], Response::HTTP_UNAUTHORIZED);
            }

            $request->attributes->set('user', $user); // attach user to request
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error: ' . $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}