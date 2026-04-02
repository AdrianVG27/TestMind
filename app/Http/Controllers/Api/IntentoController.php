<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intento;

class IntentoController extends Controller
{
    public function index()
    {
        return Intento::where('user_id', auth()->id())
            ->with('test:id,titulo')
            ->latest()
            ->get();
    }

    public function show(Intento $intento)
    {
        if ($intento->user_id !== auth()->id() && ! auth()->user()->tokenCan('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $intento->load('test');
    }
}
