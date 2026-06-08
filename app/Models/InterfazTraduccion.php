<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterfazTraduccion extends Model
{
    protected $table = 'AUX_Interfaz_Traduccion';

    protected $fillable = [
        'lenguaje_id',
        'clave',
        'valor'
    ];

    public function lenguaje(): BelongsTo
    {
        return $this->belongsTo(Lenguaje::class, 'lenguaje_id');
    }
}