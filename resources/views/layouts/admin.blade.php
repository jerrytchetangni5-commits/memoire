@php
    $liens = [
        ['route' => 'admin.dashboard', 'actif' => ['admin.dashboard'], 'icone' => 'speedometer2', 'texte' => 'Dashboard'],
        ['route' => 'admin.filieres.index', 'actif' => ['admin.filieres.*'], 'icone' => 'diagram-3', 'texte' => 'Filières'],
        ['route' => 'admin.annees-academiques.index', 'actif' => ['admin.annees-academiques.*'], 'icone' => 'calendar3', 'texte' => 'Années'],
        ['route' => 'admin.validations.index', 'actif' => ['admin.validations.*'], 'icone' => 'clipboard-check', 'texte' => 'Validations'],
    ];

    if (Auth::user()->role === 'admin') {
        $liens[] = ['route' => 'admin.memoires.create', 'actif' => ['admin.memoires.*'], 'icone' => 'cloud-upload', 'texte' => 'Archives'];
        $liens[] = ['route' => 'admin.utilisateurs.index', 'actif' => ['admin.utilisateurs.*'], 'icone' => 'people', 'texte' => 'Comptes'];
    }
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') · Bibliothèque des mémoires ENEAM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-shell">
    <a href="#contenu" class="admin-skip-link">Aller au contenu</a>

    <header class="admin-site-header">
        <div class="admin-site-header-inner">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark">ENEAM</span>
                <span class="admin-brand-name">Bibliothèque des mémoires</span>
            </a>

            <nav aria-label="Navigation principale" class="admin-desktop-nav">
                @foreach ($liens as $lien)
                    @php($actif = request()->routeIs(...$lien['actif']))
                    <a
                        class="admin-nav-link {{ $actif ? 'is-active' : '' }}"
                        href="{{ route($lien['route']) }}"
                        @if ($actif) aria-current="page" @endif
                    >
                        <i class="bi bi-{{ $lien['icone'] }}" aria-hidden="true"></i>
                        {{ $lien['texte'] }}
                    </a>
                @endforeach

                <span class="admin-current-user">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}" class="admin-logout-form">
                    @csrf
                    <button class="admin-nav-link admin-logout-button" type="submit">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        Déconnexion
                    </button>
                </form>
            </nav>

            <details class="admin-mobile-menu">
                <summary>
                    <i class="bi bi-list" aria-hidden="true"></i>
                    Menu
                </summary>
                <nav aria-label="Navigation mobile" class="admin-mobile-nav">
                    @foreach ($liens as $lien)
                        @php($actif = request()->routeIs(...$lien['actif']))
                        <a
                            class="admin-nav-link {{ $actif ? 'is-active' : '' }}"
                            href="{{ route($lien['route']) }}"
                            @if ($actif) aria-current="page" @endif
                        >
                            <i class="bi bi-{{ $lien['icone'] }}" aria-hidden="true"></i>
                            {{ $lien['texte'] }}
                        </a>
                    @endforeach

                    <span class="admin-current-user">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="admin-logout-form">
                        @csrf
                        <button class="admin-nav-link admin-logout-button" type="submit">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            Déconnexion
                        </button>
                    </form>
                </nav>
            </details>
        </div>
    </header>

    <main id="contenu" tabindex="-1" class="admin-main">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="status">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @foreach (['delete', 'user_action', 'statut'] as $errorKey)
            @error($errorKey)
                <div class="alert alert-danger" role="alert">{{ $message }}</div>
            @enderror
        @endforeach

        <div class="admin-main-heading">
            <h1>@yield('heading', 'Dashboard admin')</h1>
        </div>

        @yield('content')
    </main>

    <footer class="admin-site-footer">
        ENEAM · Bibliothèque des mémoires
    </footer>
    @stack('scripts')
</body>
</html>
