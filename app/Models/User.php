<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'name',
        'tier_codigo',
        'nickname',
        'email',
        'password',
        'avatar',
        'paypal_subscription_id',
        'paypal_status',
        'subscription_ends_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'subscription_ends_at' => 'datetime',
    ];

    public function socialAccount()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class, 'tier_codigo', 'codigo');
    }

    public function documento(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function test(): HasManyThrough
    {
        return $this->hasManyThrough(Test::class, Documento::class);
    }
}
