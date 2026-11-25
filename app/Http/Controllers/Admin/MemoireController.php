<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class MemoireController extends Controller
{
    public function create(): View
    {
        $filieres = Filiere::query()->orderBy('nom')->get(['id', 'nom']);
        $annees = AnneeAcademique::query()->orderByDesc('libelle')->get(['id', 'libelle']);
        $etudiants = User::query()
            ->where('role', 'etudiant')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'matricule', 'email', 'filiere_id']);

        return view('admin.memoires.create', compact('filieres', 'annees', 'etudiants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $hasLinkedStudent = $request->filled('user_id');

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'resume' => ['required', 'string'],
            'mots_cles' => ['nullable', 'string', 'max:255'],
            'encadreur' => ['nullable', 'string', 'max:150'],
            'autres_auteurs' => ['nullable', 'string', 'max:255'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'etudiant')),
            ],
            'auteur_nom' => [
                Rule::requiredIf(! $hasLinkedStudent),
                'nullable',
                'string',
                'max:100',
            ],
            'auteur_prenom' => [
                Rule::requiredIf(! $hasLinkedStudent),
                'nullable',
                'string',
                'max:100',
            ],
            'filiere_id' => ['required', 'integer', 'exists:filieres,id'],
            'annee_academique_id' => ['required', 'integer', 'exists:annees_academiques,id'],
            'fichier' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'auteur_nom.required' => 'Indiquez le nom de l’auteur ou sélectionnez un compte étudiant.',
            'auteur_prenom.required' => 'Indiquez le prénom de l’auteur ou sélectionnez un compte étudiant.',
            'fichier.mimes' => 'Le document doit être un fichier PDF.',
            'fichier.max' => 'Le fichier PDF ne doit pas dépasser 20 Mo.',
        ]);

        $etudiant = isset($validated['user_id'])
            ? User::query()->where('role', 'etudiant')->findOrFail($validated['user_id'])
            : null;

        if ($etudiant?->filiere_id && (int) $etudiant->filiere_id !== (int) $validated['filiere_id']) {
            throw ValidationException::withMessages([
                'filiere_id' => ['La filière doit correspondre à celle du compte étudiant sélectionné.'],
            ]);
        }

        $fichier = $validated['fichier'];
        unset($validated['fichier']);
        $path = $fichier->store('memoires', 'local');

        if (! $path) {
            throw new RuntimeException('Le fichier du mémoire n’a pas pu être enregistré.');
        }

        Memoire::create([
            ...$validated,
            'user_id' => $etudiant?->id,
            'auteur_nom' => $etudiant?->nom ?? $validated['auteur_nom'],
            'auteur_prenom' => $etudiant?->prenom ?? $validated['auteur_prenom'],
            'fichier' => $path,
            'statut' => 'valide',
            'valide_par' => $request->user()->id,
            'date_validation' => now(),
        ]);

        return redirect()->route('admin.memoires.create')
            ->with('status', 'Le mémoire a été enregistré et publié directement.');
    }
}
