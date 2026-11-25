@props(['memoire', 'href'])
@php
    $principal = trim(($memoire->auteur_prenom ?: $memoire->etudiant->prenom ?? '').' '.($memoire->auteur_nom ?: $memoire->etudiant->nom ?? ''));
    $autres = trim((string) $memoire->autres_auteurs, " ,;");
    $auteurs = $principal.($autres !== '' ? ($principal !== '' ? ', ' : '').$autres : '');
    $nbLectures = array_key_exists('lectures_count', $memoire->getAttributes()) ? (int) $memoire->lectures_count : null;
@endphp
<article class="relative flex h-full flex-col rounded-xl border border-slate-300 bg-white p-5 transition-colors duration-200 hover:border-slate-500 has-[a:focus-visible]:outline-2 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-[color:var(--color-primary)]">
    <h2 class="font-heading text-xl font-semibold leading-snug text-slate-900">
        {{-- Lien étendu à toute la carte : un seul arrêt de tabulation par mémoire --}}
        <a href="{{ $href }}" class="underline-offset-2 hover:underline focus-visible:outline-none after:absolute after:inset-0 after:content-['']">{{ $memoire->titre }}</a>
    </h2>

    @if ($auteurs !== '')
        <p class="mt-1 text-sm text-slate-600">{{ $auteurs }}</p>
    @endif

    <p class="mt-3 leading-relaxed text-slate-700">{{ Str::limit($memoire->resume, 220) }}</p>

    <ul class="mt-4 flex flex-wrap gap-2 text-sm text-slate-700">
        @if ($memoire->filiere)
            <li class="rounded-md border border-slate-300 bg-slate-50 px-2 py-1">{{ $memoire->filiere->nom }}</li>
        @endif
        @if ($memoire->anneeAcademique)
            <li class="rounded-md border border-slate-300 bg-slate-50 px-2 py-1">{{ $memoire->anneeAcademique->libelle }}</li>
        @endif
        @if (! is_null($nbLectures))
            <li class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-2 py-1">
                <x-icone name="eye" class="size-4" />
                {{ $nbLectures }} {{ $nbLectures > 1 ? 'lectures' : 'lecture' }}
            </li>
        @endif
    </ul>

    @if ($slot->isNotEmpty())
        <div class="relative z-10 mt-auto pt-4">
            {{ $slot }}
        </div>
    @endif
</article>
