<?php

namespace App\Http\Controllers\Etudiant\Concerns;

use App\Models\Memoire;
use App\Models\User;

trait VerifieAccesMemoire
{
    /** Mémoire validé et hors embargo : même règle que Memoire::scopeValides() et que les contrôleurs API. */
    protected function estPublic(Memoire $memoire): bool
    {
        return $memoire->statut === 'valide'
            && (is_null($memoire->date_embargo) || $memoire->date_embargo->isPast());
    }

    /** Accès au PDF : public OU propriétaire OU staff (règle de l'API LectureController). */
    protected function peutAcceder(Memoire $memoire, User $user): bool
    {
        return $this->estPublic($memoire)
            || $memoire->user_id === $user->id
            || in_array($user->role, ['responsable', 'admin'], true);
    }
}
