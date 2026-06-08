<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lenguaje extends Model
{
    protected $table = 'AUX_Lenguaje';

    protected $fillable = [
        'codigo',
        'descripcion'
    ];

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(
            Categoria::class,
            'AUX_Categoria_Lenguaje',
            'lenguaje_id',
            'categoria_id'
        )->withPivot('descripcion')->withTimestamps();
    }

    public function tiers(): BelongsToMany
    {
        return $this->belongsToMany(
            Tier::class,
            'AUX_Tier_Lenguaje',
            'lenguaje_id',
            'tier_id'
        )->withPivot('descripcion')->withTimestamps();
    }

    public function traduccionesInterfaz(): HasMany
    {
        return $this->hasMany(InterfazTraduccion::class, 'lenguaje_id');
    }
}
