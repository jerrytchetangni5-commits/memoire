@extends('layouts.admin')

@section('title', 'Ajouter une filière')
@section('heading', 'Ajouter une filière')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-diagram-3"></i> STRUCTURE ACADÉMIQUE</p>
                <p class="admin-banner-description">Ajoutez un parcours de formation au catalogue de la bibliothèque.</p>
            </div>
            <a class="admin-secondary-link" href="{{ route('admin.filieres.index') }}">
                <i class="bi bi-arrow-left"></i> Retour aux filières
            </a>
        </section>

        <section class="admin-panel admin-form-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">NOUVEAU PARCOURS</p>
                    <h2>Informations de la filière</h2>
                    <p>Le code et le nom seront visibles dans le catalogue administratif.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
            </header>
            <div class="admin-form-body">
                @include('admin.filieres._form', [
                    'action' => route('admin.filieres.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Créer la filière',
                ])
            </div>
        </section>
    </div>
@endsection
