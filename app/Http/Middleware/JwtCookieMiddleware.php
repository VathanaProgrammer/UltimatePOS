<?php
namespace App\Http\Middleware;

use Closure;;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->cookie('c_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated. No token'], 401);
        }

        try {
            $payload = JWTAuth::parseToken()->setToken($token)->getPayload(); // or JWTAuth::getPayload($token)
            $user = JWTAuth::manager()->getUserFromPayload($payload);

            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found'], 401);
            }

            $request->merge(['user' => $user]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error: '.$e->getMessage()], 401);
        }

        return $next($request);
    }
}