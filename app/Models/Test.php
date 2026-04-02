<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    
    public function user()
    {
        return $this->documento->user();
    }
}
