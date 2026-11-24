@extends('layouts.admin')

@section('title', 'Créer un compte utilisateur')
@section('heading', 'Créer un compte utilisateur')

@section('content')
    <div class="admin-screen">
        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-person-plus"></i> COMMUNAUTÉ ENEAM</p>
                <p class="admin-banner-description">Créez un accès adapté au rôle de chaque membre de la bibliothèque.</p>
            </div>
            <a class="admin-secondary-link" href="{{ route('admin.utilisateurs.index') }}">
                <i class="bi bi-arrow-left"></i> Retour aux utilisateurs
            </a>
        </section>

        <section class="admin-panel admin-form-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">NOUVEAU COMPTE</p>
                    <h2>Profil et accès</h2>
                    <p>Les champs requis s’adaptent au rôle sélectionné.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-person-vcard"></i></span>
            </header>
            <div class="admin-form-body">
                <aside class="admin-user-create-note" role="note">
                    <i class="bi bi-info-circle"></i>
                    <p>Les étudiants se connectent avec leur matricule et un code temporaire. Les responsables et administrateurs utilisent un mot de passe.</p>
                </aside>

                <form class="admin-form" method="POST" action="{{ route('admin.utilisateurs.store') }}">
                    @csrf

                    <section class="admin-form-section">
                        <div class="admin-form-section-heading">
                            <span>01</span>
                            <div>
                                <h3>Identité</h3>
                                <p>Les informations personnelles du compte.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 admin-form-field">
                                <label class="admin-form-label" for="prenom">Prénom</label>
                                <input
                                    class="form-control admin-form-control @error('prenom') is-invalid @enderror"
                                    id="prenom"
                                    name="prenom"
                                    type="text"
                                    maxlength="100"
                                    value="{{ old('prenom') }}"
                                    required
                                >
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 admin-form-field">
                                <label class="admin-form-label" for="nom">Nom</label>
                                <input
                                    class="form-control admin-form-control @error('nom') is-invalid @enderror"
                                    id="nom"
                                    name="nom"
                                    type="text"
                                    maxlength="100"
                                    value="{{ old('nom') }}"
                                    required
                                >
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="admin-form-field">
                            <label class="admin-form-label" for="email">Adresse e-mail</label>
                            <input
                                class="form-control admin-form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                type="email"
                                maxlength="150"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section-heading">
                            <span>02</span>
                            <div>
                                <h3>Rôle et rattachement</h3>
                                <p>Déterminez les accès et le parcours associé.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 admin-form-field">
                                <label class="admin-form-label" for="role">Rôle</label>
                                <select
                                    class="form-select admin-form-control @error('role') is-invalid @enderror"
                                    id="role"
                                    name="role"
                                    data-user-role
                                    required
                                >
                                    <option value="etudiant" @selected(old('role', 'etudiant') === 'etudiant')>Étudiant</option>
                                    <option value="responsable" @selected(old('role') === 'responsable')>Responsable</option>
                                    <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 admin-form-field">
                                <label class="admin-form-label" for="matricule">Matricule</label>
                                <input
                                    class="form-control admin-form-control @error('matricule') is-invalid @enderror"
                                    id="matricule"
                                    name="matricule"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="8"
                                    value="{{ old('matricule') }}"
                                    data-required-roles="etudiant"
                                >
                                <div class="admin-form-help">Obligatoire pour un étudiant (8 chiffres).</div>
                                @error('matricule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="admin-form-field">
                            <label class="admin-form-label" for="filiere_id">Filière</label>
                            <select
                                class="form-select admin-form-control @error('filiere_id') is-invalid @enderror"
                                id="filiere_id"
                                name="filiere_id"
                                data-required-roles="etudiant,responsable"
                            >
                                <option value="">Sélectionner une filière</option>
                                @foreach ($filieres as $filiere)
                                    <option value="{{ $filiere->id }}" @selected((string) old('filiere_id') === (string) $filiere->id)>
                                        {{ $filiere->nom }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="admin-form-help">Obligatoire pour un étudiant ou un responsable.</div>
                            @error('filiere_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>

                    <section class="admin-form-section">
                        <div class="admin-form-section-heading">
                            <span>03</span>
                            <div>
                                <h3>Sécurité du compte</h3>
                                <p>Un mot de passe est requis pour les rôles administratifs.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 admin-form-field">
                                <label class="admin-form-label" for="password">Mot de passe</label>
                                <input
                                    class="form-control admin-form-control @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    type="password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    data-required-roles="admin,responsable"
                                >
                                <div class="admin-form-help">Obligatoire pour un administrateur ou un responsable.</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 admin-form-field">
                                <label class="admin-form-label" for="password_confirmation">Confirmer le mot de passe</label>
                                <input
                                    class="form-control admin-form-control"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    data-required-roles="admin,responsable"
                                >
                            </div>
                        </div>
                    </section>

                    <div class="admin-form-actions">
                        <button class="admin-primary-link" type="submit">
                            <i class="bi bi-person-plus"></i> Créer le compte
                        </button>
                        <a class="admin-secondary-link" href="{{ route('admin.utilisateurs.index') }}">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
