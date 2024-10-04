<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActiveAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $user = $request->user()) return $next($request);

        if (! $user->last_active_at || $user->last_active_at->isPast()) {
            // $user->update(['last_active_at' => now()]);
            $user->timestamps = false;
            $user->last_active_at = now();
            $user->save();
        }

        return $next($request);
    }
}
