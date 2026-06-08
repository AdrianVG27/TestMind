<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intento extends Model
{
    protected $table = 'intento';

    protected $fillable = [
        'user_id', 
        'test_id', 
        'respuestas_usuario',
        'feedback',
        'aciertos', 
        'total_preguntas', 
        'nota', 
        'duracion_segundos'
    ];

    protected $casts = [
        'respuestas_usuario' => 'array',
        'feedback' => 'array'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo {
        return $this->belongsTo(Test::class);
    }
}
