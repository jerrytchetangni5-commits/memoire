<?php

namespace App\Http\Controllers;

use App\Models\Memoire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $memoires = Memoire::query()
            ->valides()
            ->when($request->filled('titre'), fn ($q) => $q->where('titre', 'like', '%'.$request->string('titre').'%'))
            ->when($request->filled('mots_cles'), fn ($q) => $q->where('mots_cles', 'like', '%'.$request->string('mots_cles').'%'))
            ->when($request->filled('filiere_id'), fn ($q) => $q->where('filiere_id', $request->integer('filiere_id')))
            ->when($request->filled('annee_academique_id'), fn ($q) => $q->where('annee_academique_id', $request->integer('annee_academique_id')))
            ->when($request->filled('auteur'), function ($q) use ($request) {
                $terme = $request->string('auteur');
                $q->where(function ($query) use ($terme): void {
                    $query->where('auteur_nom', 'like', "%{$terme}%")
                        ->orWhere('auteur_prenom', 'like', "%{$terme}%")
                        ->orWhereHas('etudiant', fn ($user) => $user->where('nom', 'like', "%{$terme}%")
                            ->orWhere('prenom', 'like', "%{$terme}%"));
                });
            })
            ->with(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom'])
            ->latest()
            ->paginate(15);

        return response()->json($memoires);
    }

    public function show(Memoire $memoire): JsonResponse
    {
        abort_unless(
            $memoire->statut === 'valide' && (is_null($memoire->date_embargo) || $memoire->date_embargo->isPast()),
            404
        );

        return response()->json($memoire->load(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom']));
    }
}
