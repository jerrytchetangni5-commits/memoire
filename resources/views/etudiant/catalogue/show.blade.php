@extends('layouts.etudiant')

@php
    $auteur = trim(($memoire->etudiant->prenom ?? '').' '.($memoire->etudiant->nom ?? ''));
    $motsCles = collect(preg_split('/[,;]+/', (string) $memoire->mots_cles))->map(fn ($m) => trim($m))->filter()->values();
@endphp

@section('title', $memoire->titre)
@section('heading', $memoire->titre)
@section('subtitle', $auteur !== '' ? 'Par '.$auteur : '')

@section('actions')
    <a href="{{ route('etudiant.catalogue.index') }}" class="inline-flex h-12 items-center gap-2 rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
        <x-icone name="arrow-left" class="size-5" />
        Retour au catalogue
    </a>
@endsection

@section('content')
    <div class="grid gap-8 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <aside class="space-y-6">
            <div class="space-y-3 rounded-xl border border-slate-300 bg-white p-5">
                @if ($memoire->telechargement_autorise)
                    <a href="{{ route('etudiant.memoires.telechargement', $memoire) }}" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
                        <x-icone name="download" class="size-5" />
                        Télécharger le PDF
                    </a>
                @else
                    <p class="text-sm text-slate-700">
                        Le téléchargement n’est pas autorisé pour ce mémoire. Vous pouvez le lire en ligne.
                    </p>
                @endif

                @if ($estFavori)
                    <form method="POST" action="{{ route('etudiant.favoris.destroy', $memoire) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-500 bg-white px-4 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
                            <x-icone name="heart" class="size-5 fill-current" />
                            Retirer des favoris
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('etudiant.favoris.store') }}">
                        @csrf
                        <input type="hidden" name="memoire_id" value="{{ $memoire->id }}">
                        <button type="submit" class="inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-500 bg-white px-4 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
                            <x-icone name="heart" class="size-5" />
                            Ajouter aux favoris
                        </button>
                    </form>
                @endif
            </div>

            <dl class="space-y-4 rounded-xl border border-slate-300 bg-white p-5 text-sm">
                @if ($memoire->filiere)
                    <div>
                        <dt class="font-medium text-slate-600">Filière</dt>
                        <dd class="mt-0.5 text-base text-slate-900">{{ $memoire->filiere->nom }}</dd>
                    </div>
                @endif
                @if ($memoire->anneeAcademique)
                    <div>
                        <dt class="font-medium text-slate-600">Année académique</dt>
                        <dd class="mt-0.5 text-base text-slate-900">{{ $memoire->anneeAcademique->libelle }}</dd>
                    </div>
                @endif
                @if ($memoire->encadreur)
                    <div>
                        <dt class="font-medium text-slate-600">Encadreur</dt>
                        <dd class="mt-0.5 text-base text-slate-900">{{ $memoire->encadreur }}</dd>
                    </div>
                @endif
                @if ($motsCles->isNotEmpty())
                    <div>
                        <dt class="font-medium text-slate-600">Mots-clés</dt>
                        <dd class="mt-1.5">
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($motsCles as $mot)
                                    <li class="rounded-md border border-slate-300 bg-slate-50 px-2 py-1 text-slate-800">{{ $mot }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
            </dl>
        </aside>

        <div class="min-w-0 space-y-8">
            <section aria-labelledby="titre-lecture">
                <h2 id="titre-lecture" class="sr-only">Lecture en ligne</h2>
                <iframe
                    src="{{ route('etudiant.memoires.lecture', $memoire) }}"
                    title="Lecture du mémoire : {{ $memoire->titre }}"
                    class="h-[75dvh] min-h-[28rem] w-full rounded-xl border border-slate-300 bg-white"
                ></iframe>
                <p class="mt-3 text-sm text-slate-600">
                    Le PDF ne s’affiche pas ?
                    <a href="{{ route('etudiant.memoires.lecture', $memoire) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-medium text-[color:var(--color-primary)] underline underline-offset-2">
                        Ouvrez-le dans un nouvel onglet
                        <x-icone name="external-link" class="size-4" />
                    </a>
                </p>
            </section>

            <section aria-labelledby="titre-resume" class="rounded-xl border border-slate-300 bg-white p-5 sm:p-6">
                <h2 id="titre-resume" class="font-heading text-2xl font-semibold">Résumé</h2>
                <p class="mt-3 max-w-prose whitespace-pre-line leading-relaxed text-slate-800">{{ $memoire->resume }}</p>
            </section>
        </div>
    </div>
@endsection
