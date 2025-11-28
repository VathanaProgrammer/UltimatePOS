<?php
// app/Http/Middleware/JwtCookieMiddleware.php
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
            // 1. Check if cookie exists
            if ($token = $request->cookie('c_token')) {
                JWTAuth::setToken($token);

                // 2. Authenticate user
                $user = JWTAuth::parseToken()->authenticate();

                // 3. Attach user to request
                $request->merge(['user' => $user]);
            } else {
                return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}