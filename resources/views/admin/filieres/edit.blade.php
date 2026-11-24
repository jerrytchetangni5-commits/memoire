@extends('layouts.admin')

@section('title', 'Modifier une filière')
@section('heading', 'Modifier une filière')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-diagram-3"></i> STRUCTURE ACADÉMIQUE</p>
                <p class="admin-banner-description">Mettez à jour les informations de ce parcours.</p>
            </div>
            <a class="admin-secondary-link" href="{{ route('admin.filieres.index') }}">
                <i class="bi bi-arrow-left"></i> Retour aux filières
            </a>
        </section>

        <section class="admin-panel admin-form-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">MODIFICATION</p>
                    <h2>{{ $filiere->nom }}</h2>
                    <p>Modifiez le code ou le nom du parcours.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
            </header>
            <div class="admin-form-body">
                @include('admin.filieres._form', [
                    'action' => route('admin.filieres.update', $filiere),
                    'method' => 'PUT',
                    'submitLabel' => 'Enregistrer',
                ])
            </div>
        </section>
    </div>
@endsection
