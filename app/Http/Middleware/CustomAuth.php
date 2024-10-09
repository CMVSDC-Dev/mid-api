<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CustomAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?? $request->token;
        $api_key = config('jwt.secret');

        // check if the token matches the JWT SECRET token
        if ($token === $api_key)
            return $next($request);
        else
            $message = 'Invalid JWT Secret key provided';


        try {
            // check if token matches to any registered active user account
            if (User::where(['jwt_secret' => $token, 'is_active' => true])->exists())
                return $next($request);
            else
                $message = 'Invalid Secret Key provided or account not active. Please contact Administrator';

            // authenticate the token provided
            if ($user = JWTAuth::parseToken()->authenticate()) {
                $request->attributes->set('user', $user);
                return $next($request);
            } else {
                $message = 'Unauthorized';
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $message ?? ''], 401);
        }
    }
}

