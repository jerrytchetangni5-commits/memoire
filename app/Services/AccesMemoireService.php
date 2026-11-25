<?php

namespace App\Services;

use App\Models\Memoire;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccesMemoireService
{
    /** Mémoire validé et hors embargo : même règle que Memoire::scopeValides(). */
    public function estPublic(Memoire $memoire): bool
    {
        return $memoire->statut === 'valide'
            && (is_null($memoire->date_embargo) || $memoire->date_embargo->isPast());
    }

    /**
     * Deux portes distinctes :
     *  - connecté : public OU propriétaire OU staff ; téléchargement seulement si la case est cochée ;
     *  - jeton payé : mémoire public uniquement ; lecture ET téléchargement, quelle que soit la case.
     * Le paiement passe au-dessus de la case « téléchargement autorisé » sans la remplacer pour les autres.
     */
    public function resoudre(Request $request, Memoire $memoire): ?AccesMemoire
    {
        $public = $this->estPublic($memoire);
        $user = $request->user();

        $connecte = $user !== null && (
            $public
            || $memoire->user_id === $user->id
            || in_array($user->role, ['responsable', 'admin'], true)
        );

        $paiement = $public ? $this->paiementDeLaSession($request, $memoire) : null;

        if (! $connecte && $paiement === null) {
            return null;
        }

        return new AccesMemoire(
            mode: $connecte ? AccesMemoire::CONNECTE : AccesMemoire::JETON,
            paiement: $paiement,
            peutTelecharger: $paiement !== null || ($connecte && (bool) $memoire->telechargement_autorise),
        );
    }

    /** Jeton affiché à l'utilisateur, ex. « K7QF-2MXA-9PLD-W3ZT-B8RN » (100 bits environ). */
    public function genererJeton(): string
    {
        return implode('-', str_split(strtoupper(Str::random(20)), 4));
    }

    /** Tolère les minuscules, espaces et tirets saisis par l'utilisateur. */
    public function normaliser(string $jeton): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $jeton));
    }

    public function hacher(string $jeton): string
    {
        return hash('sha256', $this->normaliser($jeton));
    }

    /** Un jeton n'ouvre que le mémoire pour lequel il a été délivré. */
    public function trouver(Memoire $memoire, string $jeton): ?Paiement
    {
        if ($this->normaliser($jeton) === '') {
            return null;
        }

        return Paiement::where('memoire_id', $memoire->id)
            ->where('statut', Paiement::REUSSI)
            ->where('jeton_hash', $this->hacher($jeton))
            ->first();
    }

    public function retenir(Request $request, Memoire $memoire, string $jeton): void
    {
        $request->session()->put("acces_memoires.{$memoire->id}", $this->normaliser($jeton));
    }

    private function paiementDeLaSession(Request $request, Memoire $memoire): ?Paiement
    {
        if (! $request->hasSession()) {
            return null;
        }

        $jeton = $request->session()->get("acces_memoires.{$memoire->id}");

        return is_string($jeton) && $jeton !== '' ? $this->trouver($memoire, $jeton) : null;
    }
}
