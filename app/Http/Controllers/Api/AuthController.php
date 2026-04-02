<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $esAdmin = str_ends_with($request->email, '@testmind.com');
        $modelo = $esAdmin ? Admin::class : User::class;
        $role = $esAdmin ? 'admin' : 'user';

        $usuario = $modelo::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages(['email' => ['Credenciales incorrectas']]);
        }

        $usuario->tokens()->delete();

        $tokenResult = $usuario->createToken('auth_token', [$role]);
        $token = $tokenResult->accessToken;

        if ($esAdmin) {
            $token->expires_at = now()->addMinutes(30);
        } else {
            $remember = $request->boolean('remember');
            $token->expires_at = $remember ? now()->addDays(30) : now()->addHour();
        }

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->plainTextToken,
            'role' => $role,
            'expires_at' => $token->expires_at,
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token', ['user'])->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado con éxito',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}
