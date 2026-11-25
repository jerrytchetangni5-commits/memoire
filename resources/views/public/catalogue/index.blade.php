@extends('layouts.etudiant')

@section('title', 'Catalogue')
@section('heading', 'Catalogue des mémoires')
@section('subtitle', 'Consultez les mémoires validés par la bibliothèque de l’ENEAM, classés du plus lu au moins lu.')

@section('content')
    @php
        $champ = 'h-12 w-full rounded-lg border border-slate-500 bg-white px-3 text-base text-slate-900';
        $libelle = 'mb-1 block text-sm font-medium text-slate-800';
    @endphp

    <form method="GET" action="{{ route('public.catalogue.index') }}" role="search" class="rounded-xl border border-slate-300 bg-white p-4 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="titre" class="{{ $libelle }}">Titre du mémoire</label>
                <input id="titre" name="titre" type="search" value="{{ request('titre') }}" class="{{ $champ }}">
            </div>
            <button type="submit" class="inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
                <x-icone name="search" class="size-5" />
                Rechercher
            </button>
        </div>

        <details class="mt-4" {{ $filtresAvances ? 'open' : '' }}>
            <summary class="inline-flex min-h-11 cursor-pointer items-center text-sm font-medium text-[color:var(--color-primary)] underline underline-offset-2">
                Plus de critères
            </summary>

            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="mots_cles" class="{{ $libelle }}">Mots-clés</label>
                    <input id="mots_cles" name="mots_cles" type="text" value="{{ request('mots_cles') }}" class="{{ $champ }}">
                </div>

                <div>
                    <label for="auteur" class="{{ $libelle }}">Auteur (nom ou prénom)</label>
                    <input id="auteur" name="auteur" type="text" value="{{ request('auteur') }}" class="{{ $champ }}">
                </div>

                <div>
                    <label for="filiere_id" class="{{ $libelle }}">Filière</label>
                    <select id="filiere_id" name="filiere_id" class="{{ $champ }}">
                        <option value="">Toutes les filières</option>
                        @foreach ($filieres as $filiere)
                            <option value="{{ $filiere->id }}" @selected((string) request('filiere_id') === (string) $filiere->id)>{{ $filiere->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="annee_academique_id" class="{{ $libelle }}">Année académique</label>
                    <select id="annee_academique_id" name="annee_academique_id" class="{{ $champ }}">
                        <option value="">Toutes les années</option>
                        @foreach ($annees as $annee)
                            <option value="{{ $annee->id }}" @selected((string) request('annee_academique_id') === (string) $annee->id)>{{ $annee->libelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </details>
    </form>

    <div class="mb-4 mt-8 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm text-slate-600">
            {{ $memoires->total() }} {{ $memoires->total() > 1 ? 'mémoires trouvés' : 'mémoire trouvé' }}
        </p>
        @if ($filtresActifs)
            <a href="{{ route('public.catalogue.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-[color:var(--color-primary)] underline underline-offset-2">
                Réinitialiser la recherche
            </a>
        @endif
    </div>

    @if ($memoires->isEmpty())
        <div class="rounded-xl border border-slate-300 bg-white p-8 text-center">
            <h2 class="font-heading text-2xl font-semibold">Aucun mémoire ne correspond</h2>
            <p class="mx-auto mt-2 max-w-prose text-slate-600">
                Essayez avec moins de critères ou un titre plus court.
            </p>
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($memoires as $memoire)
                <x-memoire-carte :memoire="$memoire" :href="route('public.catalogue.show', $memoire)" />
            @endforeach
        </div>

        {{ $memoires->links('pagination.etudiant') }}
    @endif
@endsection
