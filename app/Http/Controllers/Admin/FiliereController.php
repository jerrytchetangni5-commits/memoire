<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FiliereController extends Controller
{
    public function index(): View
    {
        $filieres = Filiere::query()
            ->withCount(['users', 'memoires'])
            ->orderBy('nom')
            ->paginate(15);

        return view('admin.filieres.index', compact('filieres'));
    }

    public function create(): View
    {
        return view('admin.filieres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'alpha_dash', 'max:20', Rule::unique('filieres', 'code')],
            'nom' => ['required', 'string', 'max:150'],
        ]);

        Filiere::create($validated);

        return redirect()->route('admin.filieres.index')->with('status', 'La filière a été créée.');
    }

    public function edit(Filiere $filiere): View
    {
        return view('admin.filieres.edit', compact('filiere'));
    }

    public function update(Request $request, Filiere $filiere): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'alpha_dash',
                'max:20',
                Rule::unique('filieres', 'code')->ignore($filiere),
            ],
            'nom' => ['required', 'string', 'max:150'],
        ]);

        $filiere->update($validated);

        return redirect()->route('admin.filieres.index')->with('status', 'La filière a été modifiée.');
    }

    public function destroy(Filiere $filiere): RedirectResponse
    {
        if ($filiere->users()->exists() || $filiere->memoires()->exists()) {
            return redirect()->route('admin.filieres.index')
                ->withErrors(['delete' => 'Cette filière est utilisée par des comptes ou des mémoires et ne peut pas être supprimée.']);
        }

        $filiere->delete();

        return redirect()->route('admin.filieres.index')->with('status', 'La filière a été supprimée.');
    }
}
