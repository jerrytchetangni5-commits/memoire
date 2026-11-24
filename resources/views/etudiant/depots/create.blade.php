@extends('layouts.etudiant')

@section('title', 'Déposer un mémoire')
@section('heading', 'Déposer un mémoire')
@section('subtitle', 'Votre dépôt est vérifié par la bibliothèque avant sa publication dans le catalogue.')

@section('actions')
    <a href="{{ route('etudiant.depots.index') }}" class="inline-flex h-12 items-center gap-2 rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
        <x-icone name="arrow-left" class="size-5" />
        Mes dépôts
    </a>
@endsection

@section('content')
    @php
        $champ = 'w-full rounded-lg border border-slate-500 bg-white px-3 text-base text-slate-900';
        $libelle = 'mb-1 block text-sm font-medium text-slate-800';
    @endphp

    @if (! $filiere)
        <div role="alert" class="flex max-w-2xl gap-3 rounded-lg border border-amber-500 bg-amber-50 p-4 text-amber-900">
            <x-icone name="triangle-alert" class="mt-0.5 size-5" />
            <p>Votre compte n’est associé à aucune filière, le dépôt est donc impossible. Contactez l’administration de l’ENEAM pour faire corriger votre compte.</p>
        </div>
    @else
        @if ($errors->any())
            <div id="resume-erreurs" role="alert" tabindex="-1" class="mb-6 max-w-2xl rounded-lg border border-red-500 bg-red-50 p-4 text-red-900">
                <p class="flex items-center gap-2 font-medium">
                    <x-icone name="circle-x" class="size-5" />
                    Le dépôt n’a pas été enregistré. Corrigez les points suivants :
                </p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                    @foreach ($errors->messages() as $nomChamp => $messages)
                        <li><a href="#{{ $nomChamp }}" class="underline underline-offset-2">{{ $messages[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('etudiant.depots.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6 rounded-xl border border-slate-300 bg-white p-5 sm:p-8">
            @csrf

            <p class="text-sm text-slate-600">Tous les champs sont obligatoires, sauf ceux marqués « facultatif ».</p>

            <x-field id="titre" label="Titre du mémoire" maxlength="255" required />

            {{-- Résumé --}}
            <div>
                <label for="resume" class="{{ $libelle }}">Résumé</label>
                <p id="resume-aide" class="mb-2 text-sm text-slate-600">Présentez en quelques lignes l’objet, la méthode et les conclusions de votre mémoire.</p>
                <textarea
                    id="resume"
                    name="resume"
                    rows="8"
                    required
                    aria-describedby="resume-aide @error('resume') resume-erreur @enderror"
                    @error('resume') aria-invalid="true" @enderror
                    class="{{ $champ }} py-2 leading-relaxed"
                >{{ old('resume') }}</textarea>
                @error('resume')
                    <p id="resume-erreur" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <x-field id="mots_cles" label="Mots-clés (facultatif)" hint="Séparés par des virgules" maxlength="255" />

            <x-field id="encadreur" label="Encadreur (facultatif)" maxlength="150" />

            {{-- Filière : imposée par le compte, affichée en lecture seule --}}
            <div>
                <label for="filiere_nom" class="{{ $libelle }}">Filière</label>
                <input id="filiere_nom" type="text" value="{{ $filiere->nom }}" readonly aria-describedby="filiere-aide" class="{{ $champ }} h-12 cursor-default border-slate-300 bg-slate-100 text-slate-700">
                <input type="hidden" name="filiere_id" value="{{ $filiere->id }}">
                <p id="filiere-aide" class="mt-1 text-sm text-slate-600">Définie par votre compte étudiant.</p>
                @error('filiere_id')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            {{-- Année académique --}}
            <div>
                <label for="annee_academique_id" class="{{ $libelle }}">Année académique</label>
                <select
                    id="annee_academique_id"
                    name="annee_academique_id"
                    required
                    @error('annee_academique_id') aria-invalid="true" aria-describedby="annee-erreur" @enderror
                    class="{{ $champ }} h-12"
                >
                    <option value="">Choisir une année</option>
                    @foreach ($annees as $annee)
                        <option value="{{ $annee->id }}" @selected((string) old('annee_academique_id') === (string) $annee->id)>{{ $annee->libelle }}</option>
                    @endforeach
                </select>
                @error('annee_academique_id')
                    <p id="annee-erreur" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fichier PDF --}}
            <div>
                <label for="fichier" class="{{ $libelle }}">Fichier du mémoire</label>
                <p id="fichier-aide" class="mb-2 text-sm text-slate-600">Format PDF, 20 Mo maximum.</p>
                <input
                    id="fichier"
                    name="fichier"
                    type="file"
                    accept="application/pdf,.pdf"
                    required
                    aria-describedby="fichier-aide @error('fichier') fichier-erreur @enderror"
                    @error('fichier') aria-invalid="true" @enderror
                    class="block w-full cursor-pointer rounded-lg border border-slate-500 bg-white text-sm text-slate-700 file:mr-4 file:h-12 file:cursor-pointer file:border-0 file:bg-slate-100 file:px-4 file:text-sm file:font-medium file:text-slate-800 hover:file:bg-slate-200"
                >
                @error('fichier')
                    <p id="fichier-erreur" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            {{-- Téléchargement --}}
            <div>
                <label for="telechargement_autorise" class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-300 p-4">
                    <input
                        id="telechargement_autorise"
                        name="telechargement_autorise"
                        type="checkbox"
                        value="1"
                        @checked(old('telechargement_autorise'))
                        class="mt-0.5 size-5 shrink-0 accent-[color:var(--color-primary)]"
                    >
                    <span>
                        <span class="block text-base font-medium text-slate-900">Autoriser le téléchargement du PDF</span>
                        <span class="mt-0.5 block text-sm text-slate-600">Si la case reste décochée, les lecteurs pourront seulement consulter votre mémoire en ligne.</span>
                    </span>
                </label>
                @error('telechargement_autorise')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-sm text-slate-600">
                À l’envoi, votre mémoire est comparé aux dépôts existants. Un contenu trop proche d’un autre mémoire est rejeté automatiquement.
            </p>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button
                    type="submit"
                    data-loading="Dépôt en cours…"
                    class="inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Déposer le mémoire
                </button>
                <a href="{{ route('etudiant.depots.index') }}" class="inline-flex h-12 items-center justify-center rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
                    Annuler
                </a>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        // Après un envoi refusé, le focus va au récapitulatif des erreurs (chaque ligne renvoie au champ concerné)
        document.getElementById('resume-erreurs')?.focus();
    </script>
@endpush
