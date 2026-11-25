@extends('layouts.etudiant')

@section('title', 'Historique de lecture')
@section('heading', 'Historique de lecture')
@section('subtitle', 'Les mémoires du catalogue que vous avez consultés, du plus récent au plus ancien.')

@section('content')
    @if ($lectures->isEmpty())
        <div class="rounded-xl border border-slate-300 bg-white p-8 text-center">
            <h2 class="font-heading text-2xl font-semibold">Vous n’avez encore lu aucun mémoire</h2>
            <p class="mx-auto mt-2 max-w-prose text-slate-600">
                Les mémoires que vous ouvrez depuis le catalogue apparaîtront ici.
            </p>
            <a href="{{ route('public.catalogue.index') }}" class="mt-6 inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
                <x-icone name="book-open" class="size-5" />
                Parcourir le catalogue
            </a>
        </div>
    @else
        <ul class="divide-y divide-slate-300 rounded-xl border border-slate-300 bg-white">
            @foreach ($lectures as $lecture)
                @php $luLe = \Illuminate\Support\Carbon::parse($lecture->lu_le); @endphp
                <li class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    @if ($lecture->memoire)
                        <a href="{{ route('public.catalogue.show', $lecture->memoire) }}" class="inline-flex min-h-11 items-center font-medium text-slate-900 underline-offset-2 hover:underline">
                            {{ $lecture->memoire->titre }}
                        </a>
                    @else
                        <span class="text-slate-600">Mémoire indisponible</span>
                    @endif
                    <time datetime="{{ $luLe->toIso8601String() }}" class="shrink-0 text-sm text-slate-600">
                        Lu le {{ $luLe->format('d/m/Y à H:i') }}
                    </time>
                </li>
            @endforeach
        </ul>

        {{ $lectures->links('pagination.etudiant') }}
    @endif
@endsection
