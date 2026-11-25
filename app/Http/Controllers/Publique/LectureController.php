<?php

namespace App\Http\Controllers\Publique;

use App\Http\Controllers\Controller;
use App\Models\Memoire;
use App\Services\AccesMemoireService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LectureController extends Controller
{
    public function __construct(private readonly AccesMemoireService $acces) {}

    // Flux PDF pour l'iframe. Ouvert aux connectés (règle historique) ET aux détenteurs d'un jeton payé.
    // Storage::response() renvoie une StreamedResponse, Storage::download() une BinaryFileResponse :
    // deux classes sœurs (ni l'une ni l'autre sous-classe de l'autre), d'où le type de retour commun Response.
    public function lire(Request $request, Memoire $memoire): Response
    {
        abort_unless($this->acces->resoudre($request, $memoire), 404);
        abort_unless(Storage::disk('local')->exists($memoire->fichier), 404);

        return Storage::disk('local')->response($memoire->fichier, $this->nomFichier($memoire));
    }

    public function telecharger(Request $request, Memoire $memoire): Response
    {
        $acces = $this->acces->resoudre($request, $memoire);

        abort_unless($acces, 404);
        abort_unless($acces->peutTelecharger, 403, 'Téléchargement désactivé pour ce mémoire.');
        abort_unless(Storage::disk('local')->exists($memoire->fichier), 404);

        return Storage::disk('local')->download($memoire->fichier, $this->nomFichier($memoire));
    }

    private function nomFichier(Memoire $memoire): string
    {
        return (Str::slug($memoire->titre) ?: 'memoire').'.pdf';
    }
}
