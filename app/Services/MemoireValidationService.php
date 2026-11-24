<?php

namespace App\Services;

use App\Models\Memoire;
use App\Models\User;
use App\Notifications\MemoireRejeteNotification;
use App\Notifications\MemoireValideNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemoireValidationService
{
    public function authorizeQueue(User $actor): void
    {
        abort_if(
            $actor->role === 'responsable' && ! $actor->filiere_id,
            403,
            'Votre compte responsable n’est associé à aucune filière.'
        );
    }

    public function authorizeMemoire(User $actor, Memoire $memoire): void
    {
        $this->authorizeQueue($actor);

        abort_if(
            $actor->role === 'responsable' && (int) $memoire->filiere_id !== (int) $actor->filiere_id,
            403,
            'Ce mémoire ne relève pas de votre filière.'
        );
    }

    public function marquerEnVerification(User $actor, Memoire $memoire): Memoire
    {
        $this->authorizeMemoire($actor, $memoire);

        return DB::transaction(function () use ($memoire): Memoire {
            $memoire = Memoire::query()->lockForUpdate()->findOrFail($memoire->id);

            if ($memoire->statut !== 'soumis') {
                throw ValidationException::withMessages([
                    'statut' => "Ce mémoire n'est plus en attente de vérification.",
                ]);
            }

            $memoire->update(['statut' => 'en_verification']);

            return $memoire;
        });
    }

    public function valider(User $actor, Memoire $memoire): Memoire
    {
        $memoire = $this->decider($actor, $memoire, 'valide');
        $memoire->etudiant->notify(new MemoireValideNotification($memoire));

        return $memoire;
    }

    public function rejeter(User $actor, Memoire $memoire, string $motif): Memoire
    {
        $memoire = $this->decider($actor, $memoire, 'rejete', $motif);
        $memoire->etudiant->notify(new MemoireRejeteNotification($memoire));

        return $memoire;
    }

    private function decider(User $actor, Memoire $memoire, string $statut, ?string $motif = null): Memoire
    {
        $this->authorizeMemoire($actor, $memoire);

        return DB::transaction(function () use ($actor, $memoire, $statut, $motif): Memoire {
            $memoire = Memoire::query()->lockForUpdate()->findOrFail($memoire->id);

            if (! in_array($memoire->statut, ['soumis', 'en_verification'], true)) {
                throw ValidationException::withMessages([
                    'statut' => 'Une décision a déjà été prise pour ce mémoire.',
                ]);
            }

            $memoire->update([
                'statut' => $statut,
                'motif_rejet' => $motif,
                'valide_par' => $actor->id,
                'date_validation' => now(),
            ]);

            return $memoire;
        });
    }
}
