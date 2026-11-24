@extends('layouts.etudiant')

@php
    $motsCles = collect(preg_split('/[,;]+/', (string) $memoire->mots_cles))->map(fn ($m) => trim($m))->filter()->values();
    $enEmbargo = $memoire->statut === 'valide' && $memoire->date_embargo && $memoire->date_embargo->isFuture();
@endphp

@section('title', $memoire->titre)
@section('heading', $memoire->titre)

@section('actions')
    <a href="{{ route('etudiant.depots.index') }}" class="inline-flex h-12 items-center gap-2 rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
        <x-icone name="arrow-left" class="size-5" />
        Mes dépôts
    </a>
@endsection

@section('content')
    @if ($memoire->statut === 'rejete' && $memoire->motif_rejet)
        <div class="mb-8 rounded-xl border border-red-500 bg-red-50 p-5 text-red-900">
            <h2 class="flex items-center gap-2 font-heading text-xl font-semibold">
                <x-icone name="circle-x" class="size-5" />
                Motif du rejet
            </h2>
            <p class="mt-2 max-w-prose whitespace-pre-line">{{ $memoire->motif_rejet }}</p>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <aside class="space-y-6">
            <div class="space-y-4 rounded-xl border border-slate-300 bg-white p-5">
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-600">Statut</p>
                    <x-statut-badge :statut="$memoire->statut" />
                </div>

                @if ($memoire->date_validation)
                    <p class="text-sm text-slate-700">
                        Décision du {{ \Illuminate\Support\Carbon::parse($memoire->date_validation)->format('d/m/Y') }}
                    </p>
                @endif

                @if ($enEmbargo)
                    <p class="text-sm text-slate-700">
                        Visible dans le catalogue à partir du {{ $memoire->date_embargo->format('d/m/Y') }}.
                    </p>
                @endif

                @if ($memoire->telechargement_autorise)
                    <a href="{{ route('etudiant.memoires.telechargement', $memoire) }}" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-lg border border-slate-500 bg-white px-4 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
                        <x-icone name="download" class="size-5" />
                        Télécharger le PDF
                    </a>
                @endif
            </div>

            <dl class="space-y-4 rounded-xl border border-slate-300 bg-white p-5 text-sm">
                <div>
                    <dt class="font-medium text-slate-600">Déposé le</dt>
                    <dd class="mt-0.5 text-base text-slate-900">{{ $memoire->created_at->format('d/m/Y') }}</dd>
                </div>
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
                <div>
                    <dt class="font-medium text-slate-600">Téléchargement par les lecteurs</dt>
                    <dd class="mt-0.5 text-base text-slate-900">{{ $memoire->telechargement_autorise ? 'Autorisé' : 'Non autorisé' }}</dd>
                </div>
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
