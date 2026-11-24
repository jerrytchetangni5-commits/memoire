<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Memoire;
use App\Notifications\MemoireRejeteNotification;
use App\Notifications\MemoireValideNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Memoire::whereIn('statut', ['soumis', 'en_verification'])
            ->with(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom,matricule']);

        if ($request->user()->filiere_id) {
            $query->where('filiere_id', $request->user()->filiere_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function show(Request $request, Memoire $memoire): JsonResponse
    {
        $this->autoriserFiliere($request, $memoire);

        return response()->json($memoire->load(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom,matricule']));
    }

    public function marquerEnVerification(Request $request, Memoire $memoire): JsonResponse
    {
        $this->autoriserFiliere($request, $memoire);

        abort_unless($memoire->statut === 'soumis', 422, "Ce mémoire n'est pas en attente de vérification.");

        $memoire->update(['statut' => 'en_verification']);

        return response()->json(['message' => 'Mémoire pris en charge.', 'memoire' => $memoire]);
    }

    public function valider(Request $request, Memoire $memoire): JsonResponse
    {
        $this->autoriserFiliere($request, $memoire);

        abort_unless(in_array($memoire->statut, ['soumis', 'en_verification'], true), 422, 'Décision déjà prise.');

        $memoire->update([
            'statut' => 'valide',
            'valide_par' => $request->user()->id,
            'date_validation' => now(),
        ]);

        $memoire->etudiant->notify(new MemoireValideNotification($memoire));

        return response()->json(['message' => 'Mémoire validé.', 'memoire' => $memoire]);
    }

    public function rejeter(Request $request, Memoire $memoire): JsonResponse
    {
        $this->autoriserFiliere($request, $memoire);

        abort_unless(in_array($memoire->statut, ['soumis', 'en_verification'], true), 422, 'Décision déjà prise.');

        $validated = $request->validate([
            'motif_rejet' => ['required', 'string', 'max:1000'],
        ]);

        $memoire->update([
            'statut' => 'rejete',
            'motif_rejet' => $validated['motif_rejet'],
            'valide_par' => $request->user()->id,
            'date_validation' => now(),
        ]);

        $memoire->etudiant->notify(new MemoireRejeteNotification($memoire));

        return response()->json(['message' => 'Mémoire rejeté.', 'memoire' => $memoire]);
    }

    private function autoriserFiliere(Request $request, Memoire $memoire): void
    {
        abort_if(
            $request->user()->filiere_id && $memoire->filiere_id !== $request->user()->filiere_id,
            403,
            'Hors de votre filière.'
        );
    }
}
