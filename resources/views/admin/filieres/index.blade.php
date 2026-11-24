@extends('layouts.admin')

@section('title', 'Gestion des filières')
@section('heading', 'Filières')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-diagram-3"></i> STRUCTURE ACADÉMIQUE</p>
                <p class="admin-banner-description">Organisez les parcours de formation et gardez le catalogue des mémoires bien classé.</p>
            </div>
            <a class="admin-primary-link" href="{{ route('admin.filieres.create') }}">
                <i class="bi bi-plus-lg"></i> Ajouter une filière
            </a>
        </section>

        <section class="admin-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">CATALOGUE</p>
                    <h2>Filières enregistrées</h2>
                    <p>{{ $filieres->total() }} {{ $filieres->total() === 1 ? 'filière' : 'filières' }} dans le catalogue.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
            </header>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Code</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Comptes</th>
                            <th scope="col">Mémoires</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filieres as $filiere)
                            <tr>
                                <td><span class="admin-code-chip">{{ $filiere->code }}</span></td>
                                <td class="admin-table-primary">{{ $filiere->nom }}</td>
                                <td>{{ number_format($filiere->users_count, 0, ',', ' ') }}</td>
                                <td>{{ number_format($filiere->memoires_count, 0, ',', ' ') }}</td>
                                <td class="text-end text-nowrap">
                                    <div class="admin-row-actions">
                                        <a
                                            class="admin-icon-action"
                                            href="{{ route('admin.filieres.edit', $filiere) }}"
                                            aria-label="Modifier la filière {{ $filiere->nom }}"
                                            title="Modifier"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.filieres.destroy', $filiere) }}"
                                            onsubmit="return confirm('Supprimer cette filière ?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="admin-icon-action admin-icon-action-danger"
                                                type="submit"
                                                aria-label="Supprimer la filière {{ $filiere->nom }}"
                                                title="Supprimer"
                                            >
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="admin-empty-state">
                                        <span class="admin-empty-icon"><i class="bi bi-diagram-3"></i></span>
                                        <h3>Aucune filière pour le moment</h3>
                                        <p>Créez une filière pour commencer à classer les mémoires.</p>
                                        <a class="admin-secondary-link" href="{{ route('admin.filieres.create') }}">
                                            Ajouter une filière <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($filieres->hasPages())
                <div class="admin-panel-footer">
                    {{ $filieres->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
@endsection
