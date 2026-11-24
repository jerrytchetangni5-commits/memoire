@extends('layouts.auth')

@section('title', 'Connexion admin')
@section('heading', 'Connexion admin')
@section('subtitle', 'Connectez-vous avec votre adresse e-mail et votre mot de passe.')

@section('content')
    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
        @csrf

        <x-field
            id="email"
            label="Adresse e-mail"
            type="email"
            autocomplete="username"
            required
        />

        <x-field
            id="password"
            label="Mot de passe"
            type="password"
            autocomplete="current-password"
            required
        />

        <button
            type="submit"
            data-loading="Connexion…"
            class="h-12 w-full cursor-pointer rounded-lg bg-[color:var(--color-primary)] px-4 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
        >
            Se connecter
        </button>
    </form>
@endsection
