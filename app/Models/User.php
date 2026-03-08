<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function socialAccount()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function documento(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function test(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Test::class, Documento::class);
    }
}
