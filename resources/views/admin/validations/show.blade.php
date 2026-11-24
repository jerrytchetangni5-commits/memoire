@extends('layouts.admin')

@section('title', 'Examiner un mémoire')
@section('heading', 'Examiner un mémoire')

@section('content')
    <div class="admin-screen admin-review-page">
        @error('statut')
            <div class="alert alert-danger" role="alert">{{ $message }}</div>
        @enderror

        <section class="admin-page-banner admin-page-banner-compact">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-clipboard2-check"></i> EXAMEN DU DOSSIER</p>
                <p class="admin-banner-description">Comparez les informations en ligne avec l’exemplaire reçu à l’école.</p>
            </div>
            <a class="admin-secondary-link" href="{{ route('admin.validations.index') }}">
                <i class="bi bi-arrow-left"></i> Retour à la file
            </a>
        </section>

        <div class="admin-review-grid">
            <div class="admin-review-main-column">
                <section class="admin-panel admin-review-document">
                    <header class="admin-panel-heading">
                        <div>
                            <p class="admin-panel-kicker">MÉMOIRE SOUMIS</p>
                            <h2>{{ $memoire->titre }}</h2>
                            <p>{{ $memoire->filiere->nom }} · {{ $memoire->anneeAcademique->libelle }}</p>
                        </div>
                        <span class="admin-status-badge {{ $memoire->statut === 'soumis' ? 'admin-status-blue' : 'admin-status-orange' }}">
                            <span></span>{{ $memoire->statut === 'soumis' ? 'Soumis' : 'En vérification' }}
                        </span>
                    </header>
                    <div class="admin-panel-body">
                        <div class="admin-review-summary">
                            <h3><i class="bi bi-text-paragraph"></i> Résumé</h3>
                            <p class="text-break">{{ $memoire->resume }}</p>
                        </div>

                        @if ($memoire->mots_cles)
                            <div class="admin-review-detail">
                                <h3>Mots-clés</h3>
                                <p>{{ $memoire->mots_cles }}</p>
                            </div>
                        @endif

                        @if ($memoire->encadreur)
                            <div class="admin-review-detail">
                                <h3>Encadreur</h3>
                                <p>{{ $memoire->encadreur }}</p>
                            </div>
                        @endif

                        <a
                            class="admin-secondary-link admin-pdf-link"
                            href="{{ route('admin.validations.fichier', $memoire) }}"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="bi bi-file-earmark-pdf"></i> Consulter le fichier PDF
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                </section>
            </div>

            <aside class="admin-review-side-column" aria-label="Actions et dossier étudiant">
                <section class="admin-panel admin-student-panel">
                    <header class="admin-panel-heading">
                        <div>
                            <p class="admin-panel-kicker">AUTEUR</p>
                            <h2>Dossier étudiant</h2>
                        </div>
                        <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-person-vcard"></i></span>
                    </header>
                    <div class="admin-panel-body">
                        <dl class="admin-student-details">
                            <dt>Étudiant</dt>
                            <dd>{{ $memoire->etudiant->name }}</dd>
                            <dt>Matricule</dt>
                            <dd>{{ $memoire->etudiant->matricule ?? '—' }}</dd>
                            <dt>E-mail</dt>
                            <dd class="text-break">{{ $memoire->etudiant->email }}</dd>
                            <dt>Filière</dt>
                            <dd>{{ $memoire->filiere->nom }} ({{ $memoire->filiere->code }})</dd>
                            <dt>Année académique</dt>
                            <dd>{{ $memoire->anneeAcademique->libelle }}</dd>
                            <dt>Déposé le</dt>
                            <dd>{{ $memoire->created_at->format('d/m/Y à H:i') }}</dd>
                        </dl>
                    </div>
                </section>

                @if ($memoire->statut === 'soumis')
                    <form
                        class="admin-panel admin-review-action admin-review-start"
                        method="POST"
                        action="{{ route('admin.validations.en-verification', $memoire) }}"
                    >
                        @csrf
                        @method('PATCH')
                        <div class="admin-panel-body">
                            <span class="admin-action-icon"><i class="bi bi-box-seam"></i></span>
                            <h2>Exemplaire reçu ?</h2>
                            <p>Indiquez que le dossier physique est arrivé à l’école pour commencer la vérification.</p>
                            <button class="admin-action-submit" type="submit">Commencer la vérification</button>
                        </div>
                    </form>
                @endif

                <form
                    class="admin-panel admin-review-action admin-review-approve"
                    method="POST"
                    action="{{ route('admin.validations.valider', $memoire) }}"
                    onsubmit="return confirm('Valider et publier ce mémoire ?')"
                >
                    @csrf
                    @method('PATCH')
                    <div class="admin-panel-body">
                        <span class="admin-action-icon"><i class="bi bi-check2-circle"></i></span>
                        <h2>Le dossier est conforme ?</h2>
                        <p>La validation rendra ce mémoire consultable dans le catalogue.</p>
                        <button class="admin-action-submit" type="submit">Valider et publier</button>
                    </div>
                </form>

                <form
                    class="admin-panel admin-review-action admin-review-reject"
                    method="POST"
                    action="{{ route('admin.validations.rejeter', $memoire) }}"
                    onsubmit="return confirm('Rejeter ce mémoire et transmettre le motif à l’étudiant ?')"
                >
                    @csrf
                    @method('PATCH')
                    <div class="admin-panel-body">
                        <span class="admin-action-icon"><i class="bi bi-exclamation-circle"></i></span>
                        <h2>Signaler une correction</h2>
                        <label class="admin-form-label" for="motif_rejet">Motif du rejet</label>
                        <textarea
                            class="form-control admin-form-control @error('motif_rejet') is-invalid @enderror"
                            id="motif_rejet"
                            name="motif_rejet"
                            rows="4"
                            maxlength="1000"
                            required
                        >{{ old('motif_rejet') }}</textarea>
                        @error('motif_rejet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button class="admin-action-submit admin-action-danger" type="submit">
                            Rejeter avec ce motif
                        </button>
                    </div>
                </form>
            </aside>
        </div>
    </div>
@endsection
