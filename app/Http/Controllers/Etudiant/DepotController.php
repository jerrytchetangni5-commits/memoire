<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Notifications\MemoireRejeteNotification;
use App\Services\SimilariteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepotController extends Controller
{
    // Seuil de rejet automatique, identique à celui de l'API MemoireController
    private const SEUIL_SIMILARITE = 20;

    public function __construct(private readonly SimilariteService $similarite) {}

    // Miroir de MemoireController::index (API)
    public function index(Request $request): View
    {
        $memoires = Memoire::where('user_id', $request->user()->id)
            ->with(['filiere', 'anneeAcademique'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('etudiant.depots.index', ['memoires' => $memoires]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('etudiant.depots.create', [
            // La filière est imposée par le compte (règle Rule::in du store) : on l'affiche en lecture seule
            'filiere' => $user->filiere_id ? Filiere::find($user->filiere_id) : null,
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(['id', 'libelle']),
        ]);
    }

    // Miroir de MemoireController::store (API)
    public function store(Request $request): RedirectResponse
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
            'fichier' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20 Mo
            'telechargement_autorise' => ['sometimes', 'boolean'],
        ]);

        $path = $request->file('fichier')->store('memoires', 'local');

        $correspondance = $this->similarite->meilleureCorrespondance(
            $validated['titre'],
            $validated['resume'],
            (int) $validated['annee_academique_id']
        );

        $seuilDepasse = ($correspondance['score'] ?? 0) > self::SEUIL_SIMILARITE;

        $memoire = Memoire::create([
            ...Arr::except($validated, ['fichier']),
            'fichier' => $path,
            'user_id' => $user->id,
            'telechargement_autorise' => $request->boolean('telechargement_autorise'),
            'similarite_score' => $correspondance['score'] ?? null,
            'memoire_similaire_id' => $correspondance['memoire_id'] ?? null,
            'statut' => $seuilDepasse ? 'rejete' : 'soumis',
            'motif_rejet' => $seuilDepasse
                ? sprintf(
                    'Rejet automatique : %.2f%% de similarité détectée avec le mémoire #%d.',
                    $correspondance['score'],
                    $correspondance['memoire_id']
                )
                : null,
            'date_validation' => $seuilDepasse ? now() : null,
        ]);

        if ($seuilDepasse) {
            $user->notify(new MemoireRejeteNotification($memoire));

            return redirect()->route('etudiant.depots.show', $memoire)
                ->with('avertissement', 'Mémoire rejeté automatiquement : similarité excessive avec un dépôt existant.');
        }

        return redirect()->route('etudiant.depots.show', $memoire)
            ->with('status', 'Mémoire déposé, en attente de vérification.');
    }

    // Miroir de MemoireController::show (API)
    public function show(Request $request, Memoire $memoire): View
    {
        abort_if($memoire->user_id !== $request->user()->id, 403);

        return view('etudiant.depots.show', [
            'memoire' => $memoire->load(['filiere', 'anneeAcademique']),
        ]);
    }
}
