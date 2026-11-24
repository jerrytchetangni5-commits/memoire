@extends('layouts.admin')

@section('title', 'Ajouter une année académique')
@section('heading', 'Ajouter une année académique')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-calendar3"></i> REPÈRES DE LA BIBLIOTHÈQUE</p>
                <p class="admin-banner-description">Ajoutez une promotion pour classer les mémoires par période académique.</p>
            </div>
            <a class="admin-secondary-link" href="{{ route('admin.annees-academiques.index') }}">
                <i class="bi bi-arrow-left"></i> Retour aux années
            </a>
        </section>

        <section class="admin-panel admin-form-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">NOUVELLE PROMOTION</p>
                    <h2>Année académique</h2>
                    <p>Utilisez le format AAAA-AAAA, par exemple 2025-2026.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-calendar3"></i></span>
            </header>
            <div class="admin-form-body">
                @include('admin.annees-academiques._form', [
                    'action' => route('admin.annees-academiques.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Créer l’année',
                ])
            </div>
        </section>
    </div>
@endsection
