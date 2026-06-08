<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'refresh.token' => \App\Http\Middleware\RefreshTokenTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error_key' => 'error.GlobalHandler_NotFound.404',
                    'message' => 'El recurso solicitado o la URL de la API no existe.',
                ], 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error_key' => 'error.GlobalHandler_Unauthorized.401',
                    'message' => 'Sesión inválida o expirada. Por favor, vuelve a autenticarte.',
                ], 401);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error_key' => 'error.GlobalHandler_Forbidden.403',
                    'message' => 'No dispones de los permisos requeridos para ejecutar esta acción.',
                ], 403);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error_key' => 'error.GlobalHandler_MethodNotAllowed.405',
                    'message' => 'El método HTTP utilizado no está admitido para este endpoint.',
                ], 405);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof ValidationException) {
                    return null;
                }

                Log::error('Excepción Crítica Global de Servidor: '.$e->getMessage());

                return response()->json([
                    'error_key' => 'error.GlobalHandler_ServerError.500',
                    'message' => 'Se ha producido un error inesperado en las dependencias del servidor.',
                ], 500);
            }
        });
    })->create();
