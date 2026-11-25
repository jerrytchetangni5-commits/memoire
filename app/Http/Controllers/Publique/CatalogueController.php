<?php

namespace App\Http\Controllers\Publique;

use App\Http\Controllers\Controller;
use App\Models\AnneeAcademique;
use App\Models\Favori;
use App\Models\Filiere;
use App\Models\Lecture;
use App\Models\Memoire;
use App\Services\AccesMemoireService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    private const FILTRES_AVANCES = ['mots_cles', 'auteur', 'filiere_id', 'annee_academique_id'];

    public function __construct(private readonly AccesMemoireService $acces) {}

    // Page publique : aucune connexion requise
    public function index(Request $request): View
    {
        $memoires = Memoire::query()
            ->valides()
            ->when($request->filled('titre'), fn ($q) => $q->where('titre', 'like', '%'.$request->string('titre').'%'))
            ->when($request->filled('mots_cles'), fn ($q) => $q->where('mots_cles', 'like', '%'.$request->string('mots_cles').'%'))
            ->when($request->filled('filiere_id'), fn ($q) => $q->where('filiere_id', $request->integer('filiere_id')))
            ->when($request->filled('annee_academique_id'), fn ($q) => $q->where('annee_academique_id', $request->integer('annee_academique_id')))
            ->when($request->filled('auteur'), function ($q) use ($request) {
                $terme = $request->string('auteur');
                // Le déposant (compte) OU les « autres auteurs » saisis à la main
                $q->where(function ($w) use ($terme) {
                    $w->whereHas('etudiant', fn ($u) => $u->where('nom', 'like', "%{$terme}%")->orWhere('prenom', 'like', "%{$terme}%"))
                        ->orWhere('auteur_nom', 'like', "%{$terme}%")
                        ->orWhere('auteur_prenom', 'like', "%{$terme}%")
                        ->orWhere('autres_auteurs', 'like', "%{$terme}%");
                });
            })
            ->with(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom'])
            ->withCount('lectures')
            // Les plus lus d'abord ; à égalité, les plus récents
            ->orderByDesc('lectures_count')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $filtresAvances = collect(self::FILTRES_AVANCES)->contains(fn ($f) => $request->filled($f));

        return view('public.catalogue.index', [
            'memoires' => $memoires,
            'filieres' => Filiere::orderBy('nom')->get(['id', 'code', 'nom']),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(['id', 'libelle']),
            'filtresAvances' => $filtresAvances,
            'filtresActifs' => $filtresAvances || $request->filled('titre'),
        ]);
    }

    // Les métadonnées sont publiques ; le PDF dépend de l'accès (connecté ou jeton payé)
    public function show(Request $request, Memoire $memoire): View
    {
        abort_unless($this->acces->estPublic($memoire), 404);

        $user = $request->user();
        $acces = $this->acces->resoudre($request, $memoire);

        // Une lecture n'est comptée que si le lecteur a réellement accès au PDF
        if ($acces) {
            $lecture = new Lecture([
                'user_id' => $user?->id,
                'memoire_id' => $memoire->id,
                'lu_le' => now(),
            ]);
            $lecture->paiement_id = $acces->paiement?->id;
            $lecture->save();
        }

        $memoire->load(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom'])->loadCount('lectures');

        return view('public.catalogue.show', [
            'memoire' => $memoire,
            'acces' => $acces,
            'estFavori' => $user
                ? Favori::where('user_id', $user->id)->where('memoire_id', $memoire->id)->exists()
                : false,
            'prix' => (int) config('memoires.prix_acces'),
            'devise' => config('memoires.devise'),
            'kkiapay' => [
                'cle' => config('services.kkiapay.public_key'), // clé publique uniquement
                'sandbox' => (bool) config('services.kkiapay.sandbox'),
            ],
        ]);
    }
}
