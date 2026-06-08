<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
        protected $table = 'AUX_Estado';

    protected $fillable = [
        'codigo',
        'valorUsado',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'codigo' => 'string',
            'valorUsado' => 'bool',
        ];
    }

    public function lenguajes(): BelongsToMany
    {
        return $this->belongsToMany(
            Lenguaje::class,
            'AUX_Estado_Lenguaje',
            'estado_id',
            'lenguaje_id'
        )
            ->withPivot('descripcion')
            ->withTimestamps();
    }

    public function lenguajeActual(): BelongsToMany
    {
        $sessionLocale = request()->user()?->currentAccessToken()?->language;

        if (!$sessionLocale) {
            $sessionLocale = request()->header('language', config('app.locale'));
        }

        return $this->belongsToMany(
            Lenguaje::class,
            'AUX_Estado_Lenguaje',
            'estado_id',
            'lenguaje_id'
        )
            ->withPivot('descripcion')
            ->where('AUX_Lenguaje.codigo', '=', $sessionLocale);
    }

    public function test(): HasMany
    {
        return $this->hasMany(Test::class, 'estado_codigo', 'codigo');
    }

    public function getDescripcionAttribute(): ?string
    {
        $traduccion = $this->lenguajeActual->first();

        return $traduccion ? $traduccion->pivot->descripcion : $this->codigo;
    }

}
