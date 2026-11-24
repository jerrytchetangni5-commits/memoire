@extends('layouts.admin')

@section('title', 'Années académiques')
@section('heading', 'Années académiques')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-calendar3"></i> REPÈRES DE LA BIBLIOTHÈQUE</p>
                <p class="admin-banner-description">Gérez les promotions et associez chaque mémoire à sa période académique.</p>
            </div>
            <a class="admin-primary-link" href="{{ route('admin.annees-academiques.create') }}">
                <i class="bi bi-plus-lg"></i> Ajouter une année
            </a>
        </section>

        <section class="admin-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">CATALOGUE</p>
                    <h2>Années académiques</h2>
                    <p>{{ $annees->total() }} {{ $annees->total() === 1 ? 'année enregistrée' : 'années enregistrées' }}.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-calendar3"></i></span>
            </header>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Année académique</th>
                            <th scope="col">Mémoires</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($annees as $annee)
                            <tr>
                                <td class="admin-table-primary">
                                    <span class="admin-year-label"><i class="bi bi-calendar2-week"></i> {{ $annee->libelle }}</span>
                                </td>
                                <td>{{ number_format($annee->memoires_count, 0, ',', ' ') }}</td>
                                <td class="text-end text-nowrap">
                                    <div class="admin-row-actions">
                                        <a
                                            class="admin-icon-action"
                                            href="{{ route('admin.annees-academiques.edit', $annee) }}"
                                            aria-label="Modifier l’année {{ $annee->libelle }}"
                                            title="Modifier"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.annees-academiques.destroy', $annee) }}"
                                            onsubmit="return confirm('Supprimer cette année académique ?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="admin-icon-action admin-icon-action-danger"
                                                type="submit"
                                                aria-label="Supprimer l’année {{ $annee->libelle }}"
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
                                <td colspan="3">
                                    <div class="admin-empty-state">
                                        <span class="admin-empty-icon"><i class="bi bi-calendar3"></i></span>
                                        <h3>Aucune année académique</h3>
                                        <p>Ajoutez une année pour pouvoir classer les mémoires de cette promotion.</p>
                                        <a class="admin-secondary-link" href="{{ route('admin.annees-academiques.create') }}">
                                            Ajouter une année <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($annees->hasPages())
                <div class="admin-panel-footer">
                    {{ $annees->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
@endsection
