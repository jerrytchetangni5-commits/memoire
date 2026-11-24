<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnneeAcademique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnneeAcademiqueController extends Controller
{
    public function index(): View
    {
        $annees = AnneeAcademique::query()
            ->withCount('memoires')
            ->orderByDesc('libelle')
            ->paginate(15);

        return view('admin.annees-academiques.index', compact('annees'));
    }

    public function create(): View
    {
        return view('admin.annees-academiques.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('annees_academiques', 'libelle'),
            ],
        ], [
            'libelle.regex' => 'Le format attendu est AAAA-AAAA, par exemple 2025-2026.',
        ]);

        AnneeAcademique::create($validated);

        return redirect()->route('admin.annees-academiques.index')
            ->with('status', 'L’année académique a été créée.');
    }

    public function edit(AnneeAcademique $anneeAcademique): View
    {
        return view('admin.annees-academiques.edit', compact('anneeAcademique'));
    }

    public function update(Request $request, AnneeAcademique $anneeAcademique): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('annees_academiques', 'libelle')->ignore($anneeAcademique),
            ],
        ], [
            'libelle.regex' => 'Le format attendu est AAAA-AAAA, par exemple 2025-2026.',
        ]);

        $anneeAcademique->update($validated);

        return redirect()->route('admin.annees-academiques.index')
            ->with('status', 'L’année académique a été modifiée.');
    }

    public function destroy(AnneeAcademique $anneeAcademique): RedirectResponse
    {
        if ($anneeAcademique->memoires()->exists()) {
            return redirect()->route('admin.annees-academiques.index')
                ->withErrors(['delete' => 'Cette année académique est liée à des mémoires et ne peut pas être supprimée.']);
        }

        $anneeAcademique->delete();

        return redirect()->route('admin.annees-academiques.index')
            ->with('status', 'L’année académique a été supprimée.');
    }
}
