<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Memoire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriController extends Controller
{
    // Miroir de FavoriController::index (API), avec les relations utiles aux cartes
    public function index(Request $request): View
    {
        $favoris = Favori::where('user_id', $request->user()->id)
            ->whereHas('memoire', fn ($query) => $query->valides())
            ->with(['memoire' => fn ($q) => $q
                ->withCount('lectures')
                ->with(['filiere', 'anneeAcademique', 'etudiant:id,nom,prenom'])])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('etudiant.favoris.index', ['favoris' => $favoris]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'memoire_id' => ['required', 'exists:memoires,id'],
        ]);

        abort_unless(
            Memoire::valides()->whereKey($validated['memoire_id'])->exists(),
            404
        );

        Favori::firstOrCreate([
            'user_id' => $request->user()->id,
            'memoire_id' => $validated['memoire_id'],
        ]);

        return back()->with('status', 'Mémoire ajouté à vos favoris.');
    }

    public function destroy(Request $request, Memoire $memoire): RedirectResponse
    {
        Favori::where('user_id', $request->user()->id)
            ->where('memoire_id', $memoire->id)
            ->delete();

        return back()->with('status', 'Mémoire retiré de vos favoris.');
    }
}
