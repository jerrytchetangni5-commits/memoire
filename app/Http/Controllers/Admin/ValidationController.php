<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Memoire;
use App\Services\MemoireValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ValidationController extends Controller
{
    private const STATUTS_EN_ATTENTE = ['soumis', 'en_verification'];

    public function index(Request $request, MemoireValidationService $validation): View
    {
        $actor = $request->user();
        $validation->authorizeQueue($actor);

        $statut = $request->query('statut');
        $query = Memoire::query()
            ->whereIn('statut', self::STATUTS_EN_ATTENTE)
            ->with(['filiere:id,nom', 'anneeAcademique:id,libelle', 'etudiant:id,nom,prenom,matricule']);

        if ($actor->role === 'responsable') {
            $query->where('filiere_id', $actor->filiere_id);
        }

        if (in_array($statut, self::STATUTS_EN_ATTENTE, true)) {
            $query->where('statut', $statut);
        } else {
            $statut = '';
        }

        $memoires = $query->orderBy('created_at')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.validations.index', compact('memoires', 'statut'));
    }

    public function show(
        Request $request,
        Memoire $memoire,
        MemoireValidationService $validation,
    ): View {
        $validation->authorizeMemoire($request->user(), $memoire);
        abort_unless(in_array($memoire->statut, self::STATUTS_EN_ATTENTE, true), 404);

        $memoire->load(['filiere:id,nom,code', 'anneeAcademique:id,libelle', 'etudiant:id,nom,prenom,matricule']);

        return view('admin.validations.show', compact('memoire'));
    }

    public function fichier(
        Request $request,
        Memoire $memoire,
        MemoireValidationService $validation,
    ): Response {
        $validation->authorizeMemoire($request->user(), $memoire);
        abort_unless(in_array($memoire->statut, self::STATUTS_EN_ATTENTE, true), 404);
        abort_unless(Storage::disk('local')->exists($memoire->fichier), 404, 'Le fichier du mémoire est introuvable.');

        return Storage::disk('local')->response($memoire->fichier, null, [
            'Content-Disposition' => 'inline; filename="'.basename($memoire->fichier).'"',
        ]);
    }

    public function marquerEnVerification(
        Request $request,
        Memoire $memoire,
        MemoireValidationService $validation,
    ): RedirectResponse {
        $validation->marquerEnVerification($request->user(), $memoire);

        return redirect()->route('admin.validations.show', $memoire)
            ->with('status', 'Le mémoire est maintenant en cours de vérification.');
    }

    public function valider(
        Request $request,
        Memoire $memoire,
        MemoireValidationService $validation,
    ): RedirectResponse {
        $validation->valider($request->user(), $memoire);

        return redirect()->route('admin.validations.index')
            ->with('status', 'Le mémoire a été validé et publié.');
    }

    public function rejeter(
        Request $request,
        Memoire $memoire,
        MemoireValidationService $validation,
    ): RedirectResponse {
        $validated = $request->validate([
            'motif_rejet' => ['required', 'string', 'max:1000'],
        ]);

        $validation->rejeter($request->user(), $memoire, $validated['motif_rejet']);

        return redirect()->route('admin.validations.index')
            ->with('status', 'Le mémoire a été rejeté et le motif a été transmis à l’étudiant.');
    }
}
