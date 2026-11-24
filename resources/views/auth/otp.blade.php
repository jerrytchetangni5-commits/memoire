@extends('layouts.auth')

@section('title', 'Code de connexion')
@section('heading', 'Vérification')
@section('subtitle', 'Entrez le code à 6 chiffres envoyé sur votre adresse e-mail.')

@section('content')
    <form method="POST" action="{{ route('login.otp.verify') }}" class="space-y-5">
        @csrf

        <x-field
            id="code"
            label="Code de vérification"
            inputmode="numeric"
            autocomplete="one-time-code"
            maxlength="6"
            required
            autofocus
            oninput="this.value = this.value.replace(/\D/g, '')"
        />

        <button
            type="submit"
            data-loading="Vérification…"
            class="h-12 w-full cursor-pointer rounded-lg bg-[color:var(--color-primary)] px-4 text-base font-medium text-white transition-colors duration-200 hover:bg-[color:var(--color-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
        >
            Se connecter
        </button>
    </form>

    <form method="POST" action="{{ route('login.otp.resend') }}" class="mt-4">
        @csrf
        <button
            type="submit"
            data-loading="Renvoi…"
            class="h-11 w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition-colors duration-200 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
        >
            Renvoyer le code
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}" class="font-medium text-[color:var(--color-primary)] underline underline-offset-2">Modifier le matricule</a>
@endsection