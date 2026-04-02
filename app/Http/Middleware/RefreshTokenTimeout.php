<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();
            
            if ($token->can('admin')) {
                $token->expires_at = now()->addMinutes(30);
            } else {
                $isRemember = $token->expires_at && $token->expires_at->diffInHours(now()) > 24;
                $token->expires_at = $isRemember ? now()->addDays(30) : now()->addHour();
            }

            $token->save();
        }

        return $next($request);
    }
}
