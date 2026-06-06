<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('Error crítico en el middleware RefreshTokenTimeout: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.RefreshTokenTimeout_handle.500',
                'message' => 'Error interno al actualizar la sesión activa en el servidor.',
            ], 500);
        }
    }
}
