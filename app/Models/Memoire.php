<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Memoire extends Model
{
    use HasFactory;

    protected $table = 'memoires';

    protected $fillable = [
        'titre',
        'resume',
        'mots_cles',
        'encadreur',
        'autres_auteurs',
        'user_id',
        'auteur_nom',
        'auteur_prenom',
        'filiere_id',
        'annee_academique_id',
        'fichier',
        'statut',
        'motif_rejet',
        'valide_par',
        'date_validation',
        'telechargement_autorise',
        'date_embargo',
        'similarite_score',
        'memoire_similaire_id',
    ];

    protected function casts(): array
    {
        return [
            'date_validation' => 'datetime',
            'date_embargo' => 'datetime',
            'telechargement_autorise' => 'boolean',
            'similarite_score' => 'float',
        ];
    }

    public function memoireSimilaire(): BelongsTo
    {
        return $this->belongsTo(Memoire::class, 'memoire_similaire_id');
    }

    public function scopeValides(Builder $query): Builder
    {
        return $query->where('statut', 'valide')
            ->where(function (Builder $q) {
                $q->whereNull('date_embargo')->orWhere('date_embargo', '<=', now());
            });
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getAuteurCompletAttribute(): string
    {
        $nom = $this->auteur_nom ?? $this->etudiant?->nom ?? '';
        $prenom = $this->auteur_prenom ?? $this->etudiant?->prenom ?? '';

        return trim("{$prenom} {$nom}");
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class, 'annee_academique_id');
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

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
}
