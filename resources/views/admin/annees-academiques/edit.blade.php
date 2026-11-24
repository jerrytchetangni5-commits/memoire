@extends('layouts.admin')

@section('title', 'Modifier une année académique')
@section('heading', 'Modifier une année académique')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-calendar3"></i> REPÈRES DE LA BIBLIOTHÈQUE</p>
                <p class="admin-banner-description">Mettez à jour la période académique de cette promotion.</p>
            </div>
            <a class="admin-secondary-link" href="{{ route('admin.annees-academiques.index') }}">
                <i class="bi bi-arrow-left"></i> Retour aux années
            </a>
        </section>

        <section class="admin-panel admin-form-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">MODIFICATION</p>
                    <h2>{{ $anneeAcademique->libelle }}</h2>
                    <p>Vérifiez le format AAAA-AAAA avant d’enregistrer.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
            </header>
            <div class="admin-form-body">
                @include('admin.annees-academiques._form', [
                    'action' => route('admin.annees-academiques.update', $anneeAcademique),
                    'method' => 'PUT',
                    'submitLabel' => 'Enregistrer',
                ])
            </div>
        </section>
    </div>
@endsection
