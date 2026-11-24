<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Memoire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class LectureController extends Controller
{
    public function read(Request $request, Memoire $memoire): Response
    {
        $user = $request->user();
        $estPublic = $memoire->statut === 'valide' && (is_null($memoire->date_embargo) || $memoire->date_embargo->isPast());
        $estProprietaire = $memoire->user_id === $user->id;
        $estStaff = in_array($user->role, ['responsable', 'admin'], true);

        abort_unless($estPublic || $estProprietaire || $estStaff, 404);

        if ($estPublic) {
            Lecture::create([
                'user_id' => $user->id,
                'memoire_id' => $memoire->id,
                'lu_le' => now(),
            ]);
        }

        return Storage::disk('local')->response($memoire->fichier, null, [
            'Content-Disposition' => 'inline; filename="'.basename($memoire->fichier).'"',
        ]);
    }

    public function download(Request $request, Memoire $memoire): Response
    {
        $user = $request->user();
        $estPublic = $memoire->statut === 'valide'
            && (is_null($memoire->date_embargo) || $memoire->date_embargo->isPast());
        $estProprietaire = $memoire->user_id === $user->id;
        $estStaff = in_array($user->role, ['responsable', 'admin'], true);

        abort_unless($estPublic || $estProprietaire || $estStaff, 404);
        abort_unless($memoire->telechargement_autorise, 403, 'Téléchargement désactivé pour ce mémoire.');

        return Storage::disk('local')->download($memoire->fichier);
    }

    public function historique(Request $request): JsonResponse
    {
        $lectures = Lecture::where('user_id', $request->user()->id)
            ->with('memoire:id,titre')
            ->latest('lu_le')
            ->paginate(15);

        return response()->json($lectures);
    }
}
