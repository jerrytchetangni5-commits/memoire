@extends('layouts.etudiant')

@section('title', 'Mes dépôts')
@section('heading', 'Mes dépôts')
@section('subtitle', 'Suivez l’avancement de la vérification de vos mémoires.')

@section('actions')
    <a href="{{ route('etudiant.depots.create') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
        <x-icone name="upload" class="size-5" />
        Déposer un mémoire
    </a>
@endsection

@section('content')
    @if ($memoires->isEmpty())
        <div class="rounded-xl border border-slate-300 bg-white p-8 text-center">
            <h2 class="font-heading text-2xl font-semibold">Vous n’avez encore déposé aucun mémoire</h2>
            <p class="mx-auto mt-2 max-w-prose text-slate-600">
                Déposez votre mémoire au format PDF. La bibliothèque le vérifie avant de le publier dans le catalogue.
            </p>
        </div>
    @else
        <ul class="space-y-4">
            @foreach ($memoires as $memoire)
                <li class="relative rounded-xl border border-slate-300 bg-white p-5 transition-colors duration-200 hover:border-slate-500 has-[a:focus-visible]:outline-2 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-[color:var(--color-primary)]">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <h2 class="font-heading text-xl font-semibold leading-snug text-slate-900">
                            <a href="{{ route('etudiant.depots.show', $memoire) }}" class="underline-offset-2 hover:underline focus-visible:outline-none after:absolute after:inset-0 after:content-['']">{{ $memoire->titre }}</a>
                        </h2>
                        <div class="shrink-0">
                            <x-statut-badge :statut="$memoire->statut" />
                        </div>
                    </div>

                    <p class="mt-2 text-sm text-slate-600">
                        Déposé le {{ $memoire->created_at->format('d/m/Y') }}
                        @if ($memoire->filiere) · {{ $memoire->filiere->nom }} @endif
                        @if ($memoire->anneeAcademique) · {{ $memoire->anneeAcademique->libelle }} @endif
                    </p>

                    @if ($memoire->statut === 'rejete' && $memoire->motif_rejet)
                        <div class="mt-4 rounded-lg border border-red-500 bg-red-50 p-4 text-sm text-red-900">
                            <p class="font-medium">Motif du rejet</p>
                            <p class="mt-1 whitespace-pre-line">{{ $memoire->motif_rejet }}</p>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        {{ $memoires->links('pagination.etudiant') }}
    @endif
@endsection
