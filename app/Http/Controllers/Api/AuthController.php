<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $domain = config('app.corporate_domain');

        $esAdmin = str_ends_with($request->email, '@'.$domain);
        $modelo = $esAdmin ? Admin::class : User::class;
        $role = $esAdmin ? 'admin' : 'user';

        $usuario = $modelo::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages(['email' => ['Credenciales incorrectas']]);
        }

        $tokenResult = $usuario->createToken('auth_token', [$role]);
        $tokenModel = $tokenResult->accessToken;

        $tokenModel->language = $request->header('language', 'es');
        $tokenModel->remember_me = $request->boolean('remember', false);

        if ($esAdmin) {
            $tokenModel->expires_at = now()->addMinutes(30);
        } else {
            $tokenModel->expires_at = $tokenModel->remember_me ? now()->addDays(30) : now()->addHour();
        }

        $tokenModel->timestamps = false;
        $tokenModel->save();

        return response()->json([
            'access_token' => $tokenResult->plainTextToken,
            'role' => $role,
            'expires_at' => $tokenModel->expires_at,
            'data' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'nickname' => $usuario->nickname ?? '',
                'email' => $usuario->email,
                'plan' => $esAdmin ? 'admin' : ($usuario->tier_codigo ?? 'FREE'),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada',
        ]);
    }

    public function register(Request $request)
    {
        $domain = config('app.corporate_domain');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:user,email',
                function ($attribute, $value, $fail) use ($domain) {
                    if (str_ends_with(strtolower($value), '@'.$domain)) {
                        $fail("El dominio @{$domain} está reservado para administración.");
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tier_codigo' => 'FREE',
        ]);

        $tokenResult = $user->createToken('auth_token', ['user']);

        return response()->json([
            'message' => 'Usuario registrado con éxito en TestMind',
            'access_token' => $tokenResult->plainTextToken,
            'role' => 'user',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'nickname' => '',
                'email' => $user->email,
                'plan' => 'FREE',
            ],
        ], 201);
    }

    public function registerAdmin(Request $request)
    {
        $domain = config('app.corporate_domain');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admin,email', 'ends_with:@'.$domain],
            'password' => ['required', Password::defaults()],
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Nuevo administrador dado de alta en el sistema',
            'admin' => $admin,
        ], 201);
    }

    public function me(Request $request)
    {
        $usuario = $request->user();
        $esAdmin = $usuario instanceof Admin;

        if (! $esAdmin && $usuario->tier_codigo !== 'FREE') {
            if ($usuario->subscription_ends_at && $usuario->subscription_ends_at->isPast()) {

                $usuario->update([
                    'tier_codigo' => 'FREE',
                    'paypal_subscription_id' => null,
                    'paypal_status' => 'EXPIRED',
                    'subscription_ends_at' => null,
                ]);

                Log::info("Ecosistema: El periodo de cortesía de {$usuario->email} ha concluido. Cuenta degradada a FREE de forma automática.");
            }
        }

        return response()->json([
            'role' => $esAdmin ? 'admin' : 'user',
            'data' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'nickname' => $usuario->nickname ?? '',
                'email' => $usuario->email,
                'plan' => $esAdmin ? 'admin' : ($usuario->tier_codigo ?? 'FREE'),
            ],
        ]);
    }
}
