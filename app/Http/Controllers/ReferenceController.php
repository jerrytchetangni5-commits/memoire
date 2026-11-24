<?php

namespace App\Http\Controllers;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use Illuminate\Http\JsonResponse;

class ReferenceController extends Controller
{
    public function filieres(): JsonResponse
    {
        return response()->json(Filiere::orderBy('nom')->get(['id', 'code', 'nom']));
    }

    public function anneesAcademiques(): JsonResponse
    {
        return response()->json(AnneeAcademique::orderByDesc('libelle')->get(['id', 'libelle']));
    }
}
