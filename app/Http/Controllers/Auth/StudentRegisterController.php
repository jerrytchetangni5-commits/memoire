<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EtudiantEneam;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentRegisterController extends Controller
{
    // Affiche le formulaire d'inscription
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
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

        // Redirection vers la connexion, matricule pré-rempli + message de succès
        return redirect()->route('login')
            ->withInput($request->only('matricule'))
            ->with('status', 'Inscription réussie. Vous pouvez maintenant vous connecter.');
    }
}