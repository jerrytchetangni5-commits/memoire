<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'matricule',
        'name',
        'email',
        'password',
        'role',
        'filiere_id',
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
        ];
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function memoiresDeposes(): HasMany
    {
        return $this->hasMany(Memoire::class, 'etudiant_id');
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
}