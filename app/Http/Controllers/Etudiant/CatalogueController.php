<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Etudiant\Concerns\VerifieAccesMemoire;
use App\Models\AnneeAcademique;
use App\Models\Favori;
use App\Models\Filiere;
use App\Models\Lecture;
use App\Models\Memoire;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    use VerifieAccesMemoire;

    private const FILTRES_AVANCES = ['mots_cles', 'auteur', 'filiere_id', 'annee_academique_id'];

    // Miroir de App\Http\Controllers\CatalogueController::index (API)
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
                $q->where(function ($query) use ($terme): void {
                    $query->where('auteur_nom', 'like', "%{$terme}%")
                        ->orWhere('auteur_prenom', 'like', "%{$terme}%")
                        ->orWhereHas('etudiant', fn ($user) => $user->where('nom', 'like', "%{$terme}%")
                            ->orWhere('prenom', 'like', "%{$terme}%"));
                });
            })
            ->with(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $filtresAvances = collect(self::FILTRES_AVANCES)->contains(fn ($f) => $request->filled($f));

        return view('etudiant.catalogue.index', [
            'memoires' => $memoires,
            // Listes déroulantes : miroir de ReferenceController
            'filieres' => Filiere::orderBy('nom')->get(['id', 'code', 'nom']),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(['id', 'libelle']),
            'filtresAvances' => $filtresAvances,
            'filtresActifs' => $filtresAvances || $request->filled('titre'),
        ]);
    }

    // Miroir de CatalogueController::show (404 si non validé ou sous embargo)
    // + enregistrement de la lecture, comme LectureController::read pour un mémoire public
    public function show(Request $request, Memoire $memoire): View
    {
        abort_unless($this->estPublic($memoire), 404);

        $user = $request->user();

        Lecture::create([
            'user_id' => $user->id,
            'memoire_id' => $memoire->id,
            'lu_le' => now(),
        ]);

        return view('etudiant.catalogue.show', [
            'memoire' => $memoire->load(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom']),
            'estFavori' => Favori::where('user_id', $user->id)->where('memoire_id', $memoire->id)->exists(),
        ]);
    }
}
