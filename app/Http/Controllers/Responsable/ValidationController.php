<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Memoire;
use App\Services\MemoireValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function index(Request $request, MemoireValidationService $validation): JsonResponse
    {
        $validation->authorizeQueue($request->user());

        $query = Memoire::whereIn('statut', ['soumis', 'en_verification'])
            ->with(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom,matricule', 'memoireSimilaire:id,titre']);

        if ($request->user()->role === 'responsable') {
            $query->where('filiere_id', $request->user()->filiere_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function show(Request $request, Memoire $memoire, MemoireValidationService $validation): JsonResponse
    {
        $validation->authorizeMemoire($request->user(), $memoire);

        return response()->json($memoire->load(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom,matricule', 'memoireSimilaire:id,titre']));
    }

    public function marquerEnVerification(
        Request $request,
        Memoire $memoire,
        MemoireValidationService $validation,
    ): JsonResponse {
        $memoire = $validation->marquerEnVerification($request->user(), $memoire);

        return response()->json(['message' => 'Mémoire pris en charge.', 'memoire' => $memoire]);
    }

    public function valider(Request $request, Memoire $memoire, MemoireValidationService $validation): JsonResponse
    {
        $memoire = $validation->valider($request->user(), $memoire);

        return response()->json(['message' => 'Mémoire validé.', 'memoire' => $memoire]);
    }

    public function rejeter(Request $request, Memoire $memoire, MemoireValidationService $validation): JsonResponse
    {
        $validated = $request->validate([
            'motif_rejet' => ['required', 'string', 'max:1000'],
        ]);

        $memoire = $validation->rejeter($request->user(), $memoire, $validated['motif_rejet']);

        return response()->json(['message' => 'Mémoire rejeté.', 'memoire' => $memoire]);
    }
}
