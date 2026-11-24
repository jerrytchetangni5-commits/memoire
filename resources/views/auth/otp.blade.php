@extends('layouts.auth')

@section('title', 'Connexion')
@section('heading', 'Connexion')
@section('subtitle', 'Entrez votre matricule pour recevoir un code par e-mail.')

@section('content')
    <form method="POST" action="{{ route('login.otp.request') }}" class="space-y-5">
        @csrf

        <x-field
            id="matricule"
            label="Matricule"
            inputmode="numeric"
            autocomplete="username"
            maxlength="8"
            required
            oninput="this.value = this.value.replace(/\D/g, '')"
        />

        <button
            type="submit"
            data-loading="Envoi du code…"
            class="h-12 w-full cursor-pointer rounded-lg bg-[color:var(--color-primary)] px-4 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
        >
            Recevoir le code
        </button>
    </form>
@endsection

@section('footer')
    Pas encore de compte ?
    <a href="{{ route('register') }}" class="font-medium text-[color:var(--color-primary)] underline underline-offset-2">S'inscrire</a>
@endsection