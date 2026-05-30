<?php

namespace App\Http\Middleware;

use App\Models\Lenguaje;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('language');

        $supportedLocales = Cache::remember('supported_locales', now()->addDay(), function () {
            return Lenguaje::pluck('Codigo')->toArray();
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
    }
}
