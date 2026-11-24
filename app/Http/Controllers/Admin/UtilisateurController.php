<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    private const ROLES = ['admin', 'responsable', 'etudiant'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = $request->query('role');

        $users = User::query()
            ->with('filiere')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('matricule', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, self::ROLES, true), fn (Builder $query) => $query->where('role', $role))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15)
            ->withQueryString();

        return view('admin.utilisateurs.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        $filieres = Filiere::query()->orderBy('nom')->get(['id', 'nom']);

        return view('admin.utilisateurs.create', compact('filieres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role');
        $isStudent = $role === 'etudiant';
        $requiresFiliere = in_array($role, ['etudiant', 'responsable'], true);
        $requiresPassword = in_array($role, ['admin', 'responsable'], true);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(self::ROLES)],
            'matricule' => [
                Rule::requiredIf($isStudent),
                'nullable',
                'digits:8',
                Rule::unique('users', 'matricule'),
            ],
            'filiere_id' => [
                Rule::requiredIf($requiresFiliere),
                'nullable',
                Rule::exists('filieres', 'id'),
            ],
            'password' => [
                Rule::excludeIf($isStudent),
                Rule::requiredIf($requiresPassword),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'filiere_id.required' => 'Une filière doit être associée à cet utilisateur.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $validated['actif'] = true;
        User::create($validated);

        return redirect()->route('admin.utilisateurs.index')
            ->with('status', 'Le compte utilisateur a été créé et activé.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors([
                'user_action' => 'Vous ne pouvez pas désactiver le compte avec lequel vous êtes connecté.',
            ]);
        }

        if ($user->role === 'admin' && $user->actif && User::query()
            ->where('role', 'admin')
            ->where('actif', true)
            ->count() === 1) {
            return back()->withErrors([
                'user_action' => 'Le dernier compte administrateur actif ne peut pas être désactivé.',
            ]);
        }

        $user->update(['actif' => ! $user->actif]);

        return back()->with('status', $user->actif
            ? 'Le compte utilisateur a été activé.'
            : 'Le compte utilisateur a été désactivé.');
    }
}
