<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean']
        ]);

        $remember = $request->boolean('remember');

        $esAdmin = str_ends_with($request->email, '@testmind.com');

        $modelo = $esAdmin ? Admin::class : User::class;
        $role = $esAdmin ? 'admin' : 'user';

        $usuario = $modelo::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas']
            ]);
        }

        $usuario->tokens()->delete();

        $tokenResult = $usuario->createToken('auth_token', [$role]);

        $plainToken = $tokenResult->plainTextToken;
        $token = $tokenResult->accessToken;

        if ($role === 'admin') {
            $token->expires_at = now()->addMinutes(30);
        } else {
            $token->remember_me = $remember;
            $token->expires_at = $remember ? now()->addDays(30) : now()->addHour();
        }

        $token->save();

        return response()->json([
            'access_token' => $plainToken,
            'token_type' => 'Bearer',
            'role' => $role,
            'remember' => $remember
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }

}
