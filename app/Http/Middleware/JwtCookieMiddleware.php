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
            if ($token = $request->cookie('c_token')) {
                $user = JWTAuth::setToken($token)->authenticate(); // parse & authenticate
                $request->merge(['user' => $user]);
            } else {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}