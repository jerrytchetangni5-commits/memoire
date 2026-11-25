<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lecture extends Model
{
    use HasFactory;

    protected $table = 'lectures';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'memoire_id',
        'paiement_id',
        'lu_le',
    ];

    protected function casts(): array
    {
        return [
            'lu_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memoire(): BelongsTo
    {
        return $this->belongsTo(Memoire::class);
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }
}