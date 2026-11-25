<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paiement extends Model
{
    public const REUSSI = 'reussi';

    public const ECHOUE = 'echoue';

    protected $fillable = [
        'memoire_id',
        'kkiapay_transaction_id',
        'montant',
        'statut',
        'jeton_hash',
    ];

    protected $hidden = ['jeton_hash'];

    protected function casts(): array
    {
        return ['montant' => 'integer'];
    }

    public function memoire(): BelongsTo
    {
        return $this->belongsTo(Memoire::class);
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }
}
