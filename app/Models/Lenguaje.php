<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lenguaje extends Model
{
    protected $table = 'AUX_Lenguaje';

    protected $fillable = ['codigo'];

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(
            Categoria::class,
            'AUX_Categoria_Lenguaje',
            'lenguaje_id',
            'categoria_id'
        )->withPivot('descripcion')->withTimestamps();
    }
}