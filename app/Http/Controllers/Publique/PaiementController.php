<?php

namespace App\Http\Controllers\Publique;

use App\Http\Controllers\Controller;
use App\Models\Memoire;
use App\Models\Paiement;
use App\Services\AccesMemoireService;
use App\Services\KkiapayService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaiementController extends Controller
{
    public function __construct(
        private readonly AccesMemoireService $acces,
        private readonly KkiapayService $kkiapay,
    ) {}

    /**
     * Reçoit la référence de transaction renvoyée par le widget, la VÉRIFIE auprès de Kkiapay côté serveur,
     * puis délivre un jeton si (et seulement si) la transaction est réussie et suffisante.
     * Rien de ce qui vient du navigateur n'est cru sur parole, sauf la référence elle-même.
     */
    public function store(Request $request, Memoire $memoire): RedirectResponse
    {
        abort_unless($this->acces->estPublic($memoire), 404);

        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'max:100', 'regex:/^[\w\-]+$/'],
        ]);

        $transactionId = $validated['transaction_id'];
        $prix = (int) config('memoires.prix_acces');

        // Une transaction ne sert qu'une fois, pour un seul mémoire
        $existant = Paiement::where('kkiapay_transaction_id', $transactionId)->first();
        if ($existant && $existant->statut === Paiement::REUSSI) {
            return $this->refus('Cette transaction a déjà été utilisée. Saisissez votre code d’accès pour ouvrir le mémoire.');
        }

        try {
            $resultat = $this->kkiapay->verifierTransaction($transactionId);
        } catch (RuntimeException $e) {
            report($e);

            return $this->refus('Le paiement n’a pas pu être confirmé pour le moment. Réessayez dans quelques instants.');
        }

        // La réponse doit concerner bien la transaction demandée
        if ($resultat['transaction_id'] !== null && $resultat['transaction_id'] !== $transactionId) {
            return $this->refus('La confirmation du paiement ne correspond pas à la transaction envoyée.');
        }

        $succes = $resultat['statut'] === 'SUCCESS';

        if ($succes && $resultat['montant'] >= $prix) {
            return $this->delivrerAcces($request, $memoire, $transactionId, $resultat['montant']);
        }

        if ($resultat['statut'] === 'FAILED' || $succes) {
            // Échec, ou paiement réussi mais montant insuffisant : on garde la trace, sans donner accès
            $this->enregistrerEchec($memoire, $transactionId, $resultat['montant']);

            return $this->refus($succes
                ? "Le montant payé est inférieur au prix du mémoire ({$prix} ".config('memoires.devise').'). Contactez la bibliothèque en indiquant la référence '.$transactionId.'.'
                : 'Le paiement n’a pas abouti. Vous pouvez réessayer.');
        }

        // PENDING, TRANSACTION_NOT_FOUND, statut inconnu… : aucun accès, aucune trace d'échec définitive
        return $this->refus('Kkiapay ne confirme pas ce paiement. S’il vient d’être effectué, patientez quelques minutes puis saisissez sa référence ci-dessous.');
    }

    // Saisie d'un code d'accès déjà obtenu (autre appareil, session expirée…)
    public function acces(Request $request, Memoire $memoire): RedirectResponse
    {
        abort_unless($this->acces->estPublic($memoire), 404);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        if (! $this->acces->trouver($memoire, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code d’accès invalide pour ce mémoire.'],
            ]);
        }

        $this->acces->retenir($request, $memoire, $validated['code']);

        return redirect()->route('public.catalogue.show', $memoire)
            ->with('status', 'Accès ouvert : vous pouvez lire ce mémoire.');
    }

    private function delivrerAcces(Request $request, Memoire $memoire, string $transactionId, int $montant): RedirectResponse
    {
        try {
            $jeton = DB::transaction(function () use ($memoire, $transactionId, $montant) {
                $paiement = Paiement::where('kkiapay_transaction_id', $transactionId)->lockForUpdate()->first();

                if ($paiement && $paiement->statut === Paiement::REUSSI) {
                    return null; // une requête concurrente a déjà délivré le jeton
                }

                $jeton = $this->acces->genererJeton();

                $paiement ??= new Paiement(['kkiapay_transaction_id' => $transactionId]);
                $paiement->fill([
                    'memoire_id' => $memoire->id,
                    'montant' => $montant,
                    'statut' => Paiement::REUSSI,
                    'jeton_hash' => $this->acces->hacher($jeton),
                ])->save();

                return $jeton;
            });
        } catch (UniqueConstraintViolationException) {
            $jeton = null;
        }

        if ($jeton === null) {
            return $this->refus('Cette transaction a déjà été utilisée. Saisissez votre code d’accès pour ouvrir le mémoire.');
        }

        $this->acces->retenir($request, $memoire, $jeton);

        // Le jeton en clair n'est affiché qu'une fois (flash) : seul son hash est conservé
        return redirect()->route('public.catalogue.show', $memoire)
            ->with('jeton_acces', $jeton)
            ->with('status', 'Paiement confirmé : le mémoire est ouvert.');
    }

    private function enregistrerEchec(Memoire $memoire, string $transactionId, int $montant): void
    {
        try {
            Paiement::firstOrCreate(
                ['kkiapay_transaction_id' => $transactionId],
                ['memoire_id' => $memoire->id, 'montant' => $montant, 'statut' => Paiement::ECHOUE, 'jeton_hash' => null],
            );
        } catch (UniqueConstraintViolationException) {
            // déjà enregistrée par une requête concurrente
        }
    }

    private function refus(string $message): RedirectResponse
    {
        return back()->withErrors(['transaction_id' => $message]);
    }
}
