@extends('layouts.admin')

@section('title', 'Dashboard admin')
@section('heading', 'Dashboard admin')

@section('content')
    <div class="admin-screen admin-dashboard">
        <section class="admin-page-banner admin-dashboard-banner">
            <div class="admin-page-banner-copy">
                <p class="admin-banner-kicker"><i class="bi bi-stars"></i> PILOTAGE DE LA BIBLIOTHÈQUE</p>
                <h2>La bibliothèque, en un coup d’œil.</h2>
                <p>Suivez les dépôts, les validations et l’activité de la communauté ENEAM.</p>
                <a class="admin-primary-link" href="{{ route('admin.validations.index') }}">
                    <i class="bi bi-clipboard-check"></i> Ouvrir la file de validation
                </a>
            </div>
            <div class="admin-page-banner-art" aria-hidden="true">
                <span class="admin-banner-orbit"></span>
                <span class="admin-banner-icon"><i class="bi bi-journal-bookmark"></i></span>
                <span class="admin-banner-art-label"><i class="bi bi-graph-up-arrow"></i> Vue d’ensemble</span>
            </div>
        </section>

        <div class="admin-stat-grid">
            @foreach ([
                ['label' => 'Mémoires déposés', 'value' => $stats['total'], 'icon' => 'bi-archive', 'tone' => 'blue'],
                ['label' => 'À traiter', 'value' => $stats['a_traiter'], 'icon' => 'bi-hourglass-split', 'tone' => 'orange'],
                ['label' => 'Validés', 'value' => $stats['valides'], 'icon' => 'bi-check-circle', 'tone' => 'green'],
                ['label' => 'Rejetés', 'value' => $stats['rejetes'], 'icon' => 'bi-x-circle', 'tone' => 'red'],
                ['label' => 'Comptes actifs', 'value' => $stats['utilisateurs_actifs'], 'icon' => 'bi-people', 'tone' => 'violet'],
            ] as $stat)
                <article class="admin-stat-card admin-stat-{{ $stat['tone'] }}">
                    <span class="admin-stat-icon"><i class="bi {{ $stat['icon'] }}"></i></span>
                    <span class="admin-stat-label">{{ $stat['label'] }}</span>
                    <strong class="admin-stat-value">{{ number_format($stat['value'], 0, ',', ' ') }}</strong>
                </article>
            @endforeach
        </div>

        <section class="admin-panel admin-chart-panel">
            <header class="admin-panel-heading">
                <div>
                    <p class="admin-panel-kicker">RÉPARTITION DES COLLECTIONS</p>
                    <h2>Mémoires par filière</h2>
                    <p>Un aperçu des travaux archivés dans chaque parcours.</p>
                </div>
                <span class="admin-panel-icon" aria-hidden="true"><i class="bi bi-bar-chart-line"></i></span>
            </header>
            <div class="admin-panel-body">
                @if ($chartData['labels'])
                    <div class="admin-chart-wrap" style="height: {{ max(count($chartData['labels']) * 42, 240) }}px">
                        <canvas id="memoires-par-filiere" aria-label="Nombre de mémoires par filière" role="img"></canvas>
                    </div>
                    <script id="memoires-par-filiere-data" type="application/json">@json($chartData)</script>
                @else
                    <div class="admin-empty-state admin-chart-empty">
                        <span class="admin-empty-icon"><i class="bi bi-bar-chart"></i></span>
                        <h3>Aucune donnée à représenter</h3>
                        <p>Aucune filière n’est encore enregistrée. Ajoutez-en pour faire apparaître la répartition des mémoires.</p>
                        <a class="admin-secondary-link" href="{{ route('admin.filieres.index') }}">
                            Voir les filières <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
