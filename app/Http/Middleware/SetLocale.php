<?php

namespace App\Http\Middleware;

use App\Models\Lenguaje;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $locale = $request->header('language');

            $supportedLocales = Cache::remember('supported_locales', now()->addDay(), function () {
                return Lenguaje::pluck('codigo')->toArray();
            });

            if ($locale && in_array($locale, $supportedLocales)) {

                $token = $request->user()?->currentAccessToken();

                if ($token && $token->language !== $locale) {
                    $token->language = $locale;

                    $token->timestamps = false;
                    $token->save();
                }
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Error crítico en el middleware SetLocale: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.SetLocale_handle.500',
                'message' => 'Error interno al sincronizar las preferencias de idioma en el servidor.',
            ], 500);
        }
    }
}
