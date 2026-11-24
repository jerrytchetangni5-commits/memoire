<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Etudiant\Concerns\VerifieAccesMemoire;
use App\Models\Lecture;
use App\Models\Memoire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LectureController extends Controller
{
    use VerifieAccesMemoire;

    // Flux PDF pour l'iframe de la page de détail (lecture inline).
    // L'enregistrement de la lecture est fait par CatalogueController::show, pas ici,
    // pour ne compter qu'une lecture par ouverture de page.
    public function lire(Request $request, Memoire $memoire): StreamedResponse
    {
        abort_unless($this->peutAcceder($memoire, $request->user()), 404);
        abort_unless(Storage::disk('local')->exists($memoire->fichier), 404);

        return Storage::disk('local')->response($memoire->fichier, $this->nomFichier($memoire));
    }

    // Miroir de LectureController::download (API)
    public function telecharger(Request $request, Memoire $memoire): StreamedResponse
    {
        abort_unless($this->peutAcceder($memoire, $request->user()), 404);
        abort_unless($memoire->telechargement_autorise, 403, 'Téléchargement désactivé pour ce mémoire.');
        abort_unless(Storage::disk('local')->exists($memoire->fichier), 404);

        return Storage::disk('local')->download($memoire->fichier, $this->nomFichier($memoire));
    }

    // Miroir de LectureController::historique (API)
    public function historique(Request $request): View
    {
        $lectures = Lecture::where('user_id', $request->user()->id)
            ->with('memoire:id,titre')
            ->latest('lu_le')
            ->paginate(15)
            ->withQueryString();

        return view('etudiant.historique.index', ['lectures' => $lectures]);
    }

    private function nomFichier(Memoire $memoire): string
    {
        return (Str::slug($memoire->titre) ?: 'memoire').'.pdf';
    }
}
