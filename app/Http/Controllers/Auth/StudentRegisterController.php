<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EtudiantEneam;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentRegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => ['required', 'digits:8'],
            'email' => ['required', 'email'],
        ]);

        $etudiant = EtudiantEneam::where('matricule', $validated['matricule'])
            ->where('email', $validated['email'])
            ->first();

        if (! $etudiant) {
            throw ValidationException::withMessages([
                'matricule' => ["Ce couple matricule/e-mail ne correspond à aucun étudiant de l'ENEAM."],
            ]);
        }

        if (User::where('matricule', $validated['matricule'])->exists()) {
            throw ValidationException::withMessages([
                'matricule' => ['Ce matricule est déjà inscrit.'],
            ]);
        }

        $user = User::create([
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'matricule' => $etudiant->matricule,
            'email' => $etudiant->email,
            'role' => 'etudiant',
            'filiere_id' => $etudiant->filiere_id,
            'actif' => true,
        ]);

        $user->notify(new WelcomeNotification());

        return response()->json([
            'message' => 'Inscription réussie.',
            'user' => $user,
        ], 201);
    }
}