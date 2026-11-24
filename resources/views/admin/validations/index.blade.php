@extends('layouts.admin')

@section('title', 'File de validation')
@section('heading', 'File de validation des mémoires')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-validation-banner">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-clipboard2-check"></i> CONTRÔLE DES DOSSIERS</p>
                <p class="admin-banner-description">Vérifiez les exemplaires déposés à l’école et traitez les demandes de publication.</p>
            </div>
            <form class="admin-banner-filter" method="GET" action="{{ route('admin.validations.index') }}">
                <label for="statut">Afficher</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="" @selected($statut === '')>Tous les mémoires à traiter</option>
                    <option value="soumis" @selected($statut === 'soumis')>Soumis</option>
                    <option value="en_verification" @selected($statut === 'en_verification')>En vérification</option>
                </select>
                <button class="admin-primary-link" type="submit">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
            </form>
        </section>

        <section class="admin-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">SUIVI DES DÉPÔTS</p>
                    <h2>Mémoires à examiner</h2>
                    <p>{{ $memoires->total() }} dossier{{ $memoires->total() === 1 ? '' : 's' }} dans cette file.</p>
                </div>
                <span class="admin-panel-icon admin-panel-icon-orange" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
            </header>
            <div class="table-responsive">
                <table class="table admin-table admin-validation-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Mémoire</th>
                            <th scope="col">Étudiant</th>
                            <th scope="col">Filière</th>
                            <th scope="col">Année académique</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Déposé le</th>
                            <th scope="col" class="text-end">Dossier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($memoires as $memoire)
                            <tr>
                                <td class="admin-table-primary">{{ $memoire->titre }}</td>
                                <td>
                                    {{ $memoire->etudiant->name }}
                                    @if ($memoire->etudiant->matricule)
                                        <span class="admin-table-secondary">{{ $memoire->etudiant->matricule }}</span>
                                    @endif
                                </td>
                                <td>{{ $memoire->filiere->nom }}</td>
                                <td>{{ $memoire->anneeAcademique->libelle }}</td>
                                <td>
                                    <span class="admin-status-badge {{ $memoire->statut === 'soumis' ? 'admin-status-blue' : 'admin-status-orange' }}">
                                        <span></span>{{ $memoire->statut === 'soumis' ? 'Soumis' : 'En vérification' }}
                                    </span>
                                </td>
                                <td>{{ $memoire->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a class="admin-table-link" href="{{ route('admin.validations.show', $memoire) }}">
                                        Examiner <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="admin-empty-state">
                                        <span class="admin-empty-icon admin-empty-icon-success"><i class="bi bi-check2-circle"></i></span>
                                        <h3>Tout est à jour</h3>
                                        <p>Aucun mémoire n’attend de validation pour le moment.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($memoires->hasPages())
                <div class="admin-panel-footer">
                    {{ $memoires->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
@endsection
