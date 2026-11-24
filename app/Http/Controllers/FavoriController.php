<?php

namespace App\Http\Controllers;

use App\Models\Favori;
use App\Models\Memoire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favoris = Favori::where('user_id', $request->user()->id)
            ->whereHas('memoire', fn ($query) => $query->valides())
            ->with('memoire:id,titre')
            ->latest()
            ->paginate(15);

        return response()->json($favoris);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'memoire_id' => ['required', 'exists:memoires,id'],
        ]);

        abort_unless(
            Memoire::valides()->whereKey($validated['memoire_id'])->exists(),
            404
        );

        $favori = Favori::firstOrCreate([
            'user_id' => $request->user()->id,
            'memoire_id' => $validated['memoire_id'],
        ]);

        return response()->json($favori, 201);
    }

    public function destroy(Request $request, Memoire $memoire): JsonResponse
    {
        Favori::where('user_id', $request->user()->id)
            ->where('memoire_id', $memoire->id)
            ->delete();

        return response()->json(['message' => 'Retiré des favoris.']);
    }
}
