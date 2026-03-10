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
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $token = $user->currentAccessToken();
            if ($token && $token->expires_at && $token->expires_at->isPast()) {
                $token->delete();
                return response()->json([
                    'message' => 'Sesión expirada por inactividad'
                ], 401);
            }
            if ($token) {
                $role = $token->abilities[0] ?? 'user';
                if ($role === 'admin'){
                    $token->expires_at = now()->addMinutes(30);
                } else {
                    $token->expires_at = $token->remember_me ? now()->addDays(30) : now()->addHour();
                }
                
                $token->save();
            }
        }
        return $next($request);
    }
}
