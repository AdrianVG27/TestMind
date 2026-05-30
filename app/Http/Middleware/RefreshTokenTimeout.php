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
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $token = $user?->currentAccessToken();

        if ($token) {
            if ($token->can('admin')) {
                $token->expires_at = now()->addMinutes(30);
            } else {
                $token->expires_at = $token->remember_me 
                    ? now()->addDays(30) 
                    : now()->addHour();
            }

            $token->timestamps = false; 
            $token->save();
        }

        return $next($request);
    }
}
