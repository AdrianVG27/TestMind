<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Test extends Model
{
    protected $table = 'test';

    protected $fillable = [
        'documento_id',
        'titulo',
        'configuracion',
        'preguntas',
        'estado'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'preguntas' => 'array',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }
    
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Documento::class,
            'id',
            'id',
            'documento_id',
            'user_id'
        );
    }
}
