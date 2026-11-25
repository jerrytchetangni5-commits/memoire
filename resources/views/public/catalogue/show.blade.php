@extends('layouts.etudiant')

@php
    $auteur = trim(($memoire->auteur_prenom ?: $memoire->etudiant->prenom ?? '').' '.($memoire->auteur_nom ?: $memoire->etudiant->nom ?? ''));
    $autres = trim((string) $memoire->autres_auteurs, " ,;");
    $motsCles = collect(preg_split('/[,;]+/', (string) $memoire->mots_cles))->map(fn ($m) => trim($m))->filter()->values();
    $nbLectures = (int) $memoire->lectures_count;
    $prixAffiche = number_format($prix, 0, ',', ' ').' '.$devise;
    $champ = 'h-12 w-full rounded-lg border border-slate-500 bg-white px-3 text-base text-slate-900';
@endphp

@section('title', $memoire->titre)
@section('heading', $memoire->titre)
@section('subtitle', trim(($auteur !== '' ? 'Par '.$auteur : '').($autres !== '' ? ($auteur !== '' ? ', avec ' : 'Avec ').$autres : '')))

@section('actions')
    <a href="{{ route('public.catalogue.index') }}" class="inline-flex h-12 items-center gap-2 rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
        <x-icone name="arrow-left" class="size-5" />
        Retour au catalogue
    </a>
@endsection

@section('content')
    <div class="grid gap-8 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <aside class="space-y-6">
            @if ($acces || auth()->check())
                <div class="space-y-3 rounded-xl border border-slate-300 bg-white p-5">
                    @if ($acces && $acces->peutTelecharger)
                        <a href="{{ route('public.memoires.telechargement', $memoire) }}" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
                            <x-icone name="download" class="size-5" />
                            Télécharger le PDF
                        </a>
                    @elseif ($acces)
                        <p class="text-sm text-slate-700">
                            Le téléchargement n’est pas autorisé pour ce mémoire. Vous pouvez le lire en ligne.
                        </p>
                    @endif

                    @auth
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
                    @endauth
                </div>
            @endif

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
                <div>
                    <dt class="font-medium text-slate-600">Lectures</dt>
                    <dd class="mt-0.5 text-base text-slate-900">{{ $nbLectures }}</dd>
                </div>
            </dl>
        </aside>

        <div class="min-w-0 space-y-8">
            {{-- Code d'accès : affiché une seule fois, juste après le paiement --}}
            @if (session('jeton_acces'))
                <section aria-labelledby="titre-jeton" class="rounded-xl border border-green-600 bg-green-50 p-5 text-green-900">
                    <h2 id="titre-jeton" class="flex items-center gap-2 font-heading text-xl font-semibold">
                        <x-icone name="circle-check" class="size-5" />
                        Votre code d’accès
                    </h2>
                    <p class="mt-2 max-w-prose">
                        Notez-le maintenant : il ne sera plus affiché. Il ouvre la lecture et le téléchargement de ce mémoire, sur n’importe quel appareil, sans limite de durée.
                    </p>
                    <label for="code-genere" class="sr-only">Code d’accès</label>
                    <input id="code-genere" type="text" readonly value="{{ session('jeton_acces') }}" onfocus="this.select()" class="mt-3 h-12 w-full max-w-md rounded-lg border border-green-700 bg-white px-3 font-mono text-lg tracking-wider text-slate-900">
                </section>
            @endif

            @if ($acces)
                {{-- Lecture intégrée : connecté, ou détenteur d'un code payé --}}
                <section aria-labelledby="titre-lecture">
                    <h2 id="titre-lecture" class="sr-only">Lecture en ligne</h2>
                    <iframe
                        src="{{ route('public.memoires.lecture', $memoire) }}"
                        title="Lecture du mémoire : {{ $memoire->titre }}"
                        class="h-[75dvh] min-h-[28rem] w-full rounded-xl border border-slate-300 bg-white"
                    ></iframe>
                    <p class="mt-3 text-sm text-slate-600">
                        Le PDF ne s’affiche pas ?
                        <a href="{{ route('public.memoires.lecture', $memoire) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-medium text-[color:var(--color-primary)] underline underline-offset-2">
                            Ouvrez-le dans un nouvel onglet
                            <x-icone name="external-link" class="size-4" />
                        </a>
                    </p>
                </section>
            @else
                {{-- Visiteur sans accès : paiement Kkiapay --}}
                <section aria-labelledby="titre-acces" class="rounded-xl border border-slate-300 bg-white p-5 sm:p-6">
                    <h2 id="titre-acces" class="font-heading text-2xl font-semibold">Lire et télécharger ce mémoire</h2>
                    <p class="mt-2 max-w-prose text-slate-700">
                        L’accès complet, en lecture en ligne et en téléchargement, coûte {{ $prixAffiche }}, à payer une seule fois.
                        Vous recevez ensuite un code d’accès personnel, valable sans limite de durée.
                    </p>

                    @error('transaction_id')
                        <div id="erreur-transaction" role="alert" class="mt-4 flex gap-3 rounded-lg border border-red-500 bg-red-50 p-4 text-sm text-red-900">
                            <x-icone name="circle-x" class="mt-0.5 size-5" />
                            <p>{{ $message }}</p>
                        </div>
                    @enderror

                    <p id="erreur-widget" role="alert" hidden class="mt-4 rounded-lg border border-red-500 bg-red-50 p-4 text-sm text-red-900">
                        Le module de paiement n’a pas pu se charger. Vérifiez votre connexion puis rechargez la page.
                    </p>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button id="btn-payer" type="button" class="inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
                            <x-icone name="credit-card" class="size-5" />
                            Payer {{ $prixAffiche }}
                        </button>
                        @guest
                            <p class="text-sm text-slate-600">
                                Étudiant de l’ENEAM ?
                                <a href="{{ route('login') }}" class="font-medium text-[color:var(--color-primary)] underline underline-offset-2">Connectez-vous</a>
                                pour lire gratuitement.
                            </p>
                        @endguest
                    </div>

                    {{-- Envoi de la référence de transaction : la confirmation réelle se fait côté serveur --}}
                    <form id="formulaire-paiement" method="POST" action="{{ route('public.paiement.store', $memoire) }}" class="hidden">
                        @csrf
                        <input type="hidden" id="transaction_id_widget" name="transaction_id">
                    </form>

                    <details class="mt-6 border-t border-slate-300 pt-4" {{ $errors->has('code') ? 'open' : '' }}>
                        <summary class="inline-flex min-h-11 cursor-pointer items-center text-sm font-medium text-[color:var(--color-primary)] underline underline-offset-2">
                            J’ai déjà payé
                        </summary>

                        <div class="mt-4 grid gap-6 md:grid-cols-2">
                            <form method="POST" action="{{ route('public.acces.store', $memoire) }}" class="space-y-2">
                                @csrf
                                <label for="code" class="block text-sm font-medium text-slate-800">J’ai un code d’accès</label>
                                <input
                                    id="code"
                                    name="code"
                                    type="text"
                                    autocomplete="off"
                                    autocapitalize="characters"
                                    spellcheck="false"
                                    required
                                    @error('code') aria-invalid="true" aria-describedby="code-erreur" @enderror
                                    class="{{ $champ }} font-mono tracking-wider"
                                >
                                @error('code')
                                    <p id="code-erreur" class="text-sm text-red-700">{{ $message }}</p>
                                @enderror
                                <button type="submit" data-loading="Vérification…" class="inline-flex h-12 cursor-pointer items-center justify-center rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
                                    Ouvrir avec mon code
                                </button>
                            </form>

                            <form method="POST" action="{{ route('public.paiement.store', $memoire) }}" class="space-y-2">
                                @csrf
                                <label for="transaction_id_manuel" class="block text-sm font-medium text-slate-800">J’ai payé mais je n’ai pas de code</label>
                                <input
                                    id="transaction_id_manuel"
                                    name="transaction_id"
                                    type="text"
                                    autocomplete="off"
                                    spellcheck="false"
                                    required
                                    aria-describedby="aide-reference"
                                    class="{{ $champ }} font-mono"
                                >
                                <p id="aide-reference" class="text-sm text-slate-600">Référence de transaction reçue de Kkiapay après le paiement.</p>
                                <button type="submit" data-loading="Vérification…" class="inline-flex h-12 cursor-pointer items-center justify-center rounded-lg border border-slate-500 bg-white px-5 text-base font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
                                    Vérifier mon paiement
                                </button>
                            </form>
                        </div>
                    </details>
                </section>
            @endif

            <section aria-labelledby="titre-resume" class="rounded-xl border border-slate-300 bg-white p-5 sm:p-6">
                <h2 id="titre-resume" class="font-heading text-2xl font-semibold">Résumé</h2>
                <p class="mt-3 max-w-prose whitespace-pre-line leading-relaxed text-slate-800">{{ $memoire->resume }}</p>
            </section>
        </div>
    </div>
@endsection

@if (! $acces)
    @push('scripts')
        <script src="https://cdn.kkiapay.me/k.js"></script>
        <script>
            (function () {
                var bouton = document.getElementById('btn-payer');
                var erreur = document.getElementById('erreur-widget');
                if (!bouton) { return; }

                bouton.addEventListener('click', function () {
                    if (typeof openKkiapayWidget !== 'function') {
                        erreur.hidden = false;
                        return;
                    }
                    // Seule la clé PUBLIQUE est exposée ici. La confirmation du paiement est faite par le serveur.
                    openKkiapayWidget({
                        amount: {{ $prix }},
                        key: @json($kkiapay['cle']),
                        sandbox: @json($kkiapay['sandbox']),
                        position: 'center',
                        theme: '#1e3a5f'
                    });
                });

                if (typeof addSuccessListener === 'function') {
                    addSuccessListener(function (reponse) {
                        document.getElementById('transaction_id_widget').value = reponse.transactionId;
                        document.getElementById('formulaire-paiement').submit();
                    });
                }
            })();
        </script>
    @endpush
@endif
