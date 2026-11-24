<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $memoireCounts = Memoire::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->map(static fn ($count): int => (int) $count);

        $filieres = Filiere::query()
            ->withCount('memoires')
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return view('admin.dashboard', [
            'stats' => [
                'total' => array_sum($memoireCounts->all()),
                'a_traiter' => $memoireCounts->get('soumis', 0) + $memoireCounts->get('en_verification', 0),
                'valides' => $memoireCounts->get('valide', 0),
                'rejetes' => $memoireCounts->get('rejete', 0),
                'utilisateurs_actifs' => User::query()->where('actif', true)->count(),
            ],
            'chartData' => [
                'labels' => $filieres->pluck('nom')->all(),
                'values' => $filieres->pluck('memoires_count')
                    ->map(static fn ($count): int => (int) $count)
                    ->all(),
            ],
        ]);
    }
}
