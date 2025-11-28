<?php
// app/Http/Middleware/JwtCookieMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($token = $request->cookie('c_token')) {
            JWTAuth::setToken($token);
        }
        return $next($request);
    }
}