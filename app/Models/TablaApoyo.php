<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TablaApoyo extends Model
{
    protected $table = 'TablaApoyo';

    protected $fillable = [
        'nombreTA',
        'descripcion',
        'tieneLenguajes'
    ];
}
