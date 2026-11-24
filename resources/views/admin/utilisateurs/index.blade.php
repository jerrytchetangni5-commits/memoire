@extends('layouts.admin')

@section('title', 'Gestion des utilisateurs')
@section('heading', 'Utilisateurs')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-people"></i> COMMUNAUTÉ ENEAM</p>
                <p class="admin-banner-description">Gérez les comptes, les rôles et l’accès à la bibliothèque numérique.</p>
            </div>
            <a class="admin-primary-link" href="{{ route('admin.utilisateurs.create') }}">
                <i class="bi bi-person-plus"></i> Créer un compte
            </a>
        </section>

        <section class="admin-filter-panel">
            <form class="admin-user-filters" method="GET" action="{{ route('admin.utilisateurs.index') }}">
                <div class="admin-filter-search">
                    <label class="visually-hidden" for="user-search">Rechercher un utilisateur</label>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        class="form-control"
                        id="user-search"
                        name="q"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Nom, e-mail ou matricule"
                    >
                </div>
                <div class="admin-filter-role">
                    <label class="visually-hidden" for="user-role">Filtrer par rôle</label>
                    <select class="form-select" id="user-role" name="role">
                        <option value="">Tous les rôles</option>
                        <option value="etudiant" @selected($role === 'etudiant')>Étudiant</option>
                        <option value="responsable" @selected($role === 'responsable')>Responsable</option>
                        <option value="admin" @selected($role === 'admin')>Administrateur</option>
                    </select>
                </div>
                <button class="admin-secondary-link" type="submit">
                    <i class="bi bi-funnel"></i> Rechercher
                </button>
            </form>
        </section>

        <section class="admin-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">COMPTES ET ACCÈS</p>
                    <h2>Utilisateurs</h2>
                    <p>{{ $users->total() }} compte{{ $users->total() === 1 ? '' : 's' }} affiché{{ $users->total() === 1 ? '' : 's' }}.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-person-lines-fill"></i></span>
            </header>
            <div class="table-responsive">
                <table class="table admin-table admin-users-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">Matricule</th>
                            <th scope="col">Rôle</th>
                            <th scope="col">Filière</th>
                            <th scope="col">État</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="admin-table-primary">{{ $user->name }}</td>
                                <td class="admin-user-email">{{ $user->email }}</td>
                                <td>{{ $user->matricule ?? '—' }}</td>
                                <td>
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="admin-role-badge admin-role-admin"><i class="bi bi-shield-check"></i> Administrateur</span>
                                            @break
                                        @case('responsable')
                                            <span class="admin-role-badge admin-role-manager"><i class="bi bi-person-gear"></i> Responsable</span>
                                            @break
                                        @default
                                            <span class="admin-role-badge admin-role-student"><i class="bi bi-mortarboard"></i> Étudiant</span>
                                    @endswitch
                                </td>
                                <td>{{ $user->filiere?->nom ?? '—' }}</td>
                                <td>
                                    <span class="admin-status-badge {{ $user->actif ? 'admin-status-green' : 'admin-status-muted' }}">
                                        <span></span>{{ $user->actif ? 'Actif' : 'Désactivé' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($user->is(Auth::user()))
                                        <span class="admin-self-label"><i class="bi bi-person-check"></i> Vous</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.utilisateurs.statut', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                class="admin-toggle-action {{ $user->actif ? 'is-deactivate' : 'is-activate' }}"
                                                type="submit"
                                            >
                                                <i class="bi {{ $user->actif ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                                {{ $user->actif ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="admin-empty-state">
                                        <span class="admin-empty-icon"><i class="bi bi-person-search"></i></span>
                                        <h3>Aucun utilisateur trouvé</h3>
                                        <p>Essayez un autre terme de recherche ou créez un nouveau compte.</p>
                                        <a class="admin-secondary-link" href="{{ route('admin.utilisateurs.create') }}">
                                            Créer un compte <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="admin-panel-footer">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
@endsection
