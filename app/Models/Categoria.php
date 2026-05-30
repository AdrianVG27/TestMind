<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'AUX_Categoria';

    protected $fillable = [
        'codigo',
        'valorUsado',
    ];

    public function lenguajes(): BelongsToMany
    {
        return $this->belongsToMany(
            Lenguaje::class,
            'AUX_Categoria_Lenguaje',
            'categoria_id',
            'lenguaje_id'
        )
            ->withPivot('descripcion')
            ->withTimestamps();
    }

    public function lenguajeActual(): BelongsToMany
    {
        $sessionLocale = request()->user()?->currentAccessToken()?->languaje;

        if (!$sessionLocale) {
            $sessionLocale = request()->header('language', config('app.locale'));
        }

        return $this->belongsToMany(
            Lenguaje::class,
            'AUX_Categoria_Lenguaje',
            'categoria_id',
            'lenguaje_id'
        )
            ->withPivot('descripcion')
            ->where('AUX_Lenguaje.codigo', '=', $sessionLocale);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }

    public function getDescripcionAttribute(): ?string
    {
        $traduccion = $this->lenguajeActual->first();

        return $traduccion ? $traduccion->pivot->descripcion : $this->codigo;
    }
}
