@extends('layouts.admin')

@section('title', 'Déposer un mémoire')
@section('heading', 'Archives académiques')

@section('content')
    <div class="archive-page">
        <header class="archive-hero">
            <div class="archive-hero-copy">
                <p class="archive-eyebrow"><span></span> COLLECTION PATRIMONIALE ENEAM</p>
                <h2>Les idées d’hier inspirent demain.</h2>
                <p class="archive-hero-description">
                    Référencez et préservez les anciens travaux de recherche dans la bibliothèque numérique de l’ENEAM.
                </p>
                <div class="archive-hero-badges">
                    <span><i class="bi bi-patch-check"></i> Publication immédiate</span>
                    <span><i class="bi bi-file-earmark-pdf"></i> PDF jusqu’à 20 Mo</span>
                </div>
            </div>
            <div class="archive-hero-art" aria-hidden="true">
                <div class="archive-orbit archive-orbit-one"></div>
                <div class="archive-orbit archive-orbit-two"></div>
                <div class="archive-book-mark">
                    <i class="bi bi-journal-richtext"></i>
                </div>
                <div class="archive-art-note">
                    <i class="bi bi-check2-circle"></i>
                    <span>Une mémoire<br>qui se transmet</span>
                </div>
            </div>
        </header>

        <ol class="archive-flow" aria-label="Étapes du dépôt">
            <li class="is-current">
                <span class="archive-flow-number">01</span>
                <span>Informations</span>
            </li>
            <li>
                <span class="archive-flow-number">02</span>
                <span>Classement</span>
            </li>
            <li>
                <span class="archive-flow-number">03</span>
                <span>Document PDF</span>
            </li>
        </ol>

        @if ($filieres->isEmpty() || $annees->isEmpty())
            <aside class="archive-prerequisite" role="status">
                <span class="archive-prerequisite-icon"><i class="bi bi-info-circle"></i></span>
                <div class="archive-prerequisite-copy">
                    <h2>Préparez votre catalogue</h2>
                    <p>Ajoutez les éléments manquants avant de publier un mémoire.</p>
                    <div class="archive-prerequisite-actions">
                        @if ($filieres->isEmpty())
                            <a href="{{ route('admin.filieres.create') }}">
                                Ajouter une filière <i class="bi bi-arrow-up-right"></i>
                            </a>
                        @endif
                        @if ($annees->isEmpty())
                            <a href="{{ route('admin.annees-academiques.create') }}">
                                Ajouter une année <i class="bi bi-arrow-up-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </aside>
        @endif

        <section class="archive-form-card" aria-labelledby="archive-form-title">
            <div class="archive-form-heading">
                <span class="archive-form-heading-icon"><i class="bi bi-pencil-square"></i></span>
                <div>
                    <p class="archive-section-kicker">DÉPÔT ADMINISTRATIF</p>
                    <h2 id="archive-form-title">Informations du mémoire</h2>
                    <p>Renseignez les détails qui permettront de retrouver ce travail.</p>
                </div>
                <span class="archive-required-legend"><span aria-hidden="true">*</span> Champ obligatoire</span>
            </div>

            <form
                class="archive-form"
                method="POST"
                action="{{ route('admin.memoires.store') }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="archive-form-section">
                    <div class="archive-section-heading">
                        <span class="archive-section-number">01</span>
                        <div>
                            <h3>Auteur et titre</h3>
                            <p>Identifiez le mémoire et son auteur.</p>
                        </div>
                    </div>

                    <div class="archive-field">
                        <label class="archive-label" for="titre">
                            Titre du mémoire <span aria-hidden="true">*</span>
                        </label>
                        <input
                            class="form-control archive-control @error('titre') is-invalid @enderror"
                            id="titre"
                            name="titre"
                            type="text"
                            maxlength="255"
                            value="{{ old('titre') }}"
                            placeholder="Ex. Gestion des ressources humaines dans les PME"
                            required
                        >
                        @error('titre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="archive-field">
                        <label class="archive-label" for="user_id">Compte étudiant associé <span class="archive-optional">Facultatif</span></label>
                        <select
                            class="form-select archive-control @error('user_id') is-invalid @enderror"
                            id="user_id"
                            name="user_id"
                            data-author-user
                        >
                            <option value="">Aucun compte — saisir l’auteur manuellement</option>
                            @foreach ($etudiants as $etudiant)
                                <option value="{{ $etudiant->id }}" @selected((string) old('user_id') === (string) $etudiant->id)>
                                    {{ $etudiant->name }} · {{ $etudiant->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row archive-author-fields" data-author-fields>
                        <div class="col-md-6 archive-field">
                            <label class="archive-label" for="auteur_prenom">
                                Prénom de l’auteur <span aria-hidden="true">*</span>
                            </label>
                            <input
                                class="form-control archive-control @error('auteur_prenom') is-invalid @enderror"
                                id="auteur_prenom"
                                name="auteur_prenom"
                                type="text"
                                maxlength="100"
                                value="{{ old('auteur_prenom') }}"
                                placeholder="Prénom"
                                data-required-without-user
                            >
                            @error('auteur_prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 archive-field">
                            <label class="archive-label" for="auteur_nom">
                                Nom de l’auteur <span aria-hidden="true">*</span>
                            </label>
                            <input
                                class="form-control archive-control @error('auteur_nom') is-invalid @enderror"
                                id="auteur_nom"
                                name="auteur_nom"
                                type="text"
                                maxlength="100"
                                value="{{ old('auteur_nom') }}"
                                placeholder="Nom"
                                data-required-without-user
                            >
                            @error('auteur_nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <p class="archive-field-hint" data-author-help>
                        Ces deux champs sont requis si l’auteur n’a pas de compte étudiant.
                    </p>
                </div>

                <div class="archive-form-section">
                    <div class="archive-section-heading">
                        <span class="archive-section-number">02</span>
                        <div>
                            <h3>Classement et résumé</h3>
                            <p>Ces informations facilitent la recherche dans le catalogue.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 archive-field">
                            <label class="archive-label" for="filiere_id">
                                Filière <span aria-hidden="true">*</span>
                            </label>
                            <select
                                class="form-select archive-control @error('filiere_id') is-invalid @enderror"
                                id="filiere_id"
                                name="filiere_id"
                                required
                            >
                                <option value="">Sélectionner une filière</option>
                                @foreach ($filieres as $filiere)
                                    <option value="{{ $filiere->id }}" @selected((string) old('filiere_id') === (string) $filiere->id)>
                                        {{ $filiere->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('filiere_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 archive-field">
                            <label class="archive-label" for="annee_academique_id">
                                Année académique <span aria-hidden="true">*</span>
                            </label>
                            <select
                                class="form-select archive-control @error('annee_academique_id') is-invalid @enderror"
                                id="annee_academique_id"
                                name="annee_academique_id"
                                required
                            >
                                <option value="">Sélectionner une année</option>
                                @foreach ($annees as $annee)
                                    <option value="{{ $annee->id }}" @selected((string) old('annee_academique_id') === (string) $annee->id)>
                                        {{ $annee->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('annee_academique_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="archive-field">
                        <label class="archive-label" for="resume">
                            Résumé <span aria-hidden="true">*</span>
                        </label>
                        <textarea
                            class="form-control archive-control archive-textarea @error('resume') is-invalid @enderror"
                            id="resume"
                            name="resume"
                            rows="5"
                            placeholder="Présentez brièvement le sujet, la démarche et les principaux résultats…"
                            required
                        >{{ old('resume') }}</textarea>
                        @error('resume')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 archive-field">
                            <label class="archive-label" for="mots_cles">
                                Mots-clés <span class="archive-optional">Facultatif</span>
                            </label>
                            <input
                                class="form-control archive-control @error('mots_cles') is-invalid @enderror"
                                id="mots_cles"
                                name="mots_cles"
                                type="text"
                                maxlength="255"
                                value="{{ old('mots_cles') }}"
                                placeholder="Ex. comptabilité, audit, PME"
                            >
                            @error('mots_cles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 archive-field">
                            <label class="archive-label" for="encadreur">
                                Encadreur <span class="archive-optional">Facultatif</span>
                            </label>
                            <input
                                class="form-control archive-control @error('encadreur') is-invalid @enderror"
                                id="encadreur"
                                name="encadreur"
                                type="text"
                                maxlength="150"
                                value="{{ old('encadreur') }}"
                                placeholder="Nom de l’encadreur"
                            >
                            @error('encadreur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="archive-field">
                        <label class="archive-label" for="autres_auteurs">
                            Autres auteurs <span class="archive-optional">Facultatif</span>
                        </label>
                        <input
                            class="form-control archive-control @error('autres_auteurs') is-invalid @enderror"
                            id="autres_auteurs"
                            name="autres_auteurs"
                            type="text"
                            maxlength="255"
                            value="{{ old('autres_auteurs') }}"
                            placeholder="Ex. KOFFI Jean, DOSSA Marie"
                        >
                        @error('autres_auteurs')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="archive-form-section archive-document-section">
                    <div class="archive-section-heading">
                        <span class="archive-section-number">03</span>
                        <div>
                            <h3>Document à archiver</h3>
                            <p>Importez le fichier intégral du mémoire.</p>
                        </div>
                    </div>

                    <label class="archive-dropzone @error('fichier') is-invalid @enderror" for="fichier" data-archive-dropzone>
                        <input
                            class="archive-file-input"
                            id="fichier"
                            name="fichier"
                            type="file"
                            accept="application/pdf,.pdf"
                            aria-describedby="fichier-help"
                            data-archive-file
                            required
                        >
                        <span class="archive-dropzone-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                        <span class="archive-dropzone-copy">
                            <strong data-archive-file-name aria-live="polite">Glissez votre PDF ici</strong>
                            <span>ou cliquez pour choisir un fichier</span>
                        </span>
                        <span class="archive-dropzone-action">Parcourir</span>
                    </label>
                    <p class="archive-field-hint" id="fichier-help">Format PDF uniquement · Taille maximale : 20 Mo</p>
                    @error('fichier')
                        <div class="archive-file-error" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="archive-form-footer">
                    <p><i class="bi bi-shield-check"></i> Le mémoire sera publié dès son enregistrement.</p>
                    <button
                        class="btn archive-submit"
                        type="submit"
                        @disabled($filieres->isEmpty() || $annees->isEmpty())
                    >
                        <i class="bi bi-cloud-upload"></i>
                        <span>Déposer et publier</span>
                        <i class="bi bi-arrow-right archive-submit-arrow"></i>
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
