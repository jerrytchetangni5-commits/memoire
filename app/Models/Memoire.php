<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memoire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'memoires';

    protected $fillable = [
        'titre',
        'resume',
        'mots_cles',
        'filiere_id',
        'annee_academique_id',
        'etudiant_id',
        'fichier_path',
        'statut',
        'motif_rejet',
        'valide_par',
        'date_validation',
        'acces_restreint',
        'telechargement_autorise',
        'embargo_jusqua',
        'nb_consultations',
    ];

    protected function casts(): array
    {
        return [
            'date_validation' => 'datetime',
            'embargo_jusqua' => 'date',
            'acces_restreint' => 'boolean',
            'telechargement_autorise' => 'boolean',
        ];
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class, 'annee_academique_id');
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    public function utilisateursFavoris(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favoris')->withTimestamps();
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }
}