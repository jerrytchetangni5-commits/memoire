@extends('layouts.auth')

@section('title', 'Inscription')
@section('heading', 'Inscription')
@section('subtitle', "Réservée aux étudiants de l'ENEAM.")

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf

        <x-field
            id="matricule"
            label="Matricule"
            hint="8 chiffres"
            inputmode="numeric"
            autocomplete="username"
            maxlength="8"
            required
            oninput="this.value = this.value.replace(/\D/g, '')"
        />

        <x-field
            id="email"
            type="email"
            label="E-mail institutionnel"
            autocomplete="email"
            required
        />

        <button
            type="submit"
            data-loading="Inscription…"
            class="h-12 w-full cursor-pointer rounded-lg bg-[color:var(--color-primary)] px-4 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
        >
            S'inscrire
        </button>
    </form>
@endsection

@section('footer')
    Déjà inscrit ?
    <a href="{{ route('login') }}" class="font-medium text-[color:var(--color-primary)] underline underline-offset-2">Se connecter</a>
@endsection