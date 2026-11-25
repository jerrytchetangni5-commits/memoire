<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoriqueController extends Controller
{
    // Miroir de LectureController::historique (API) : uniquement les lectures de l'étudiant connecté
    public function index(Request $request): View
    {
        $lectures = Lecture::where('user_id', $request->user()->id)
            ->with('memoire:id,titre')
            ->latest('lu_le')
            ->paginate(15)
            ->withQueryString();

        return view('etudiant.historique.index', ['lectures' => $lectures]);
    }
}
