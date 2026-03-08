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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 1. Detectamos si es un correo de admin por el dominio
        $esAdmin = str_ends_with($request->email, '@testmind.com');

        // 2. Elegimos el modelo y el "role" según el dominio
        $modelo = $esAdmin ? Admin::class : User::class;
        $role = $esAdmin ? 'admin' : 'user';

        // 3. Buscamos al usuario en la tabla correspondiente
        $usuario = $modelo::where('email', $request->email)->first();

        // 4. Validamos contraseña
        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // 5. Generamos el Token de Sanctum incluyendo el rol en las habilidades (abilities)
        $token = $usuario->createToken('auth_token', [$role])->plainTextToken;

        // 6. Respondemos a Angular con el token y el rol para que sepa a dónde redirigir
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $role,
            'user' => $usuario,
        ]);
    }
}
