<?php

namespace App\Http\Controllers;

use App\Models\Memoire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemoireController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $memoires = Memoire::where('user_id', $request->user()->id)
            ->with(['filiere', 'anneeAcademique'])
            ->latest()
            ->paginate(15);

        return response()->json($memoires);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if(is_null($user->filiere_id), 422, "Votre compte étudiant n'est associé à aucune filière.");

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'resume' => ['required', 'string'],
            'mots_cles' => ['nullable', 'string', 'max:255'],
            'encadreur' => ['nullable', 'string', 'max:150'],
            'filiere_id' => ['required', 'integer', Rule::in([$user->filiere_id])],
            'annee_academique_id' => ['required', 'exists:annees_academiques,id'],
            'fichier' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20 Mo, taille non fixée par le cahier des charges
        ]);

        $path = $request->file('fichier')->store('memoires', 'local');

        $data = $validated;
        unset($data['fichier']);
        $data['fichier'] = $path;
        $data['user_id'] = $user->id;
        $data['filiere_id'] = $user->filiere_id;
        $data['statut'] = 'soumis';

        $memoire = Memoire::create($data);

        return response()->json([
            'message' => 'Mémoire déposé, en attente de vérification.',
            'memoire' => $memoire,
        ], 201);
    }

    public function show(Request $request, Memoire $memoire): JsonResponse
    {
        abort_if($memoire->user_id !== $request->user()->id, 403);

        return response()->json($memoire->load(['filiere', 'anneeAcademique']));
    }
}
