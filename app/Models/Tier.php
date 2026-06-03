<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tier extends Model
{
    protected $table = 'AUX_Tier';

    protected $fillable = [
        'codigo',
        'conf',
        'valorUsado',
    ];

    public function lenguajes(): BelongsToMany
    {
        return $this->belongsToMany(Lenguaje::class, 'AUX_Tier_Lenguaje', 'tier_id', 'lenguaje_id')
            ->withPivot('descripcion')
            ->withTimestamps();
    }

    public function lenguajeActual(): BelongsToMany
    {
        $sessionLocale = request()->user()?->currentAccessToken()?->language ?? request()->header('language', config('app.locale'));

        return $this->belongsToMany(Lenguaje::class, 'AUX_Tier_Lenguaje', 'tier_id', 'lenguaje_id')
            ->withPivot('descripcion')
            ->where('AUX_Lenguaje.codigo', '=', $sessionLocale);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tier_id');
    }

    public function getDescripcionAttribute(): ?string
    {
        $traduccion = $this->lenguajeActual->first();

        return $traduccion ? $traduccion->pivot->descripcion : $this->codigo;
    }
}
