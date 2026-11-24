<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'matricule',
        'email',
        'password',
        'role',
        'filiere_id',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function memoiresDeposes(): HasMany
    {
        return $this->hasMany(Memoire::class, 'user_id');
    }

    public function memoiresValides(): HasMany
    {
        return $this->hasMany(Memoire::class, 'valide_par');
    }

    public function favoris(): BelongsToMany
    {
        return $this->belongsToMany(Memoire::class, 'favoris')->withTimestamps();
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class);
    }
}