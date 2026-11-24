@props(['statut'])
@php
    // Libellé + icône + couleurs : le statut n'est jamais indiqué par la seule couleur
    [$libelle, $icone, $classes] = match ($statut) {
        'soumis' => ['Soumis', 'send', 'border-slate-400 bg-slate-100 text-slate-800'],
        'en_verification' => ['En vérification', 'eye', 'border-amber-500 bg-amber-50 text-amber-900'],
        'valide' => ['Validé', 'circle-check', 'border-green-600 bg-green-50 text-green-900'],
        'rejete' => ['Rejeté', 'circle-x', 'border-red-500 bg-red-50 text-red-900'],
        default => [ucfirst((string) $statut), 'clock', 'border-slate-400 bg-slate-100 text-slate-800'],
    };
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium {{ $classes }}">
    <x-icone :name="$icone" class="size-4" />
    {{ $libelle }}
</span>
