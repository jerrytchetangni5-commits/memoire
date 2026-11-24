@extends('layouts.etudiant')

@section('title', 'Mes favoris')
@section('heading', 'Mes favoris')
@section('subtitle', 'Les mémoires que vous avez gardés de côté.')

@section('content')
    @if ($favoris->isEmpty())
        <div class="rounded-xl border border-slate-300 bg-white p-8 text-center">
            <h2 class="font-heading text-2xl font-semibold">Aucun favori pour le moment</h2>
            <p class="mx-auto mt-2 max-w-prose text-slate-600">
                Depuis la page d’un mémoire, utilisez « Ajouter aux favoris » pour le retrouver ici.
            </p>
            <a href="{{ route('etudiant.catalogue.index') }}" class="mt-6 inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)]">
                <x-icone name="book-open" class="size-5" />
                Parcourir le catalogue
            </a>
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($favoris as $favori)
                <x-memoire-carte :memoire="$favori->memoire" :href="route('etudiant.catalogue.show', $favori->memoire)">
                    <form method="POST" action="{{ route('etudiant.favoris.destroy', $favori->memoire) }}">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            aria-label="Retirer des favoris : {{ $favori->memoire->titre }}"
                            class="inline-flex h-11 cursor-pointer items-center gap-2 rounded-lg border border-slate-500 bg-white px-4 text-sm font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50"
                        >
                            <x-icone name="heart" class="size-4 fill-current" />
                            Retirer des favoris
                        </button>
                    </form>
                </x-memoire-carte>
            @endforeach
        </div>

        {{ $favoris->links('pagination.etudiant') }}
    @endif
@endsection
