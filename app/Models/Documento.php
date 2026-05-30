<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    protected $table = 'documento';

    protected $fillable = [
        'nombre',
        'isPublic',
        'user_id',
        'categoria_id',
        'path',
    ];

    protected $hidden = [
        'path',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'categoria_id' => 'integer',
            'isPublic' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function test(): HasMany
    {
        return $this->hasMany(Test::class);
    }
}
