<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') · Bibliothèque des mémoires ENEAM</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Afficher ou masquer le menu">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="text-body-secondary">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Déconnexion</button>
                    </form>
                </div>
            </div>
        </nav>

        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a class="brand-link" href="{{ route('admin.dashboard') }}">
                    <span class="brand-text fw-light">ENEAM · Mémoires</span>
                </a>
            </div>

            <div class="sidebar-wrapper">
                <nav class="mt-2" aria-label="Navigation principale">
                    <ul class="nav sidebar-menu flex-column" role="menu" data-lte-toggle="treeview" data-accordion="false">
                        <li class="nav-item">
                            <a
                                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}"
                                @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                            >
                                <i class="nav-icon bi bi-speedometer2"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link {{ request()->routeIs('admin.filieres.*') ? 'active' : '' }}"
                                href="{{ route('admin.filieres.index') }}"
                                @if (request()->routeIs('admin.filieres.*')) aria-current="page" @endif
                            >
                                <i class="nav-icon bi bi-diagram-3"></i>
                                <p>Filières</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link {{ request()->routeIs('admin.annees-academiques.*') ? 'active' : '' }}"
                                href="{{ route('admin.annees-academiques.index') }}"
                                @if (request()->routeIs('admin.annees-academiques.*')) aria-current="page" @endif
                            >
                                <i class="nav-icon bi bi-calendar3"></i>
                                <p>Années académiques</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link {{ request()->routeIs('admin.validations.*') ? 'active' : '' }}"
                                href="{{ route('admin.validations.index') }}"
                                @if (request()->routeIs('admin.validations.*')) aria-current="page" @endif
                            >
                                <i class="nav-icon bi bi-clipboard-check"></i>
                                <p>Validations</p>
                            </a>
                        </li>
                        @if (Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('admin.memoires.*') ? 'active' : '' }}"
                                    href="{{ route('admin.memoires.create') }}"
                                    @if (request()->routeIs('admin.memoires.*')) aria-current="page" @endif
                                >
                                    <i class="nav-icon bi bi-cloud-upload"></i>
                                    <p>Dépôt admin</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('admin.utilisateurs.*') ? 'active' : '' }}"
                                    href="{{ route('admin.utilisateurs.index') }}"
                                    @if (request()->routeIs('admin.utilisateurs.*')) aria-current="page" @endif
                                >
                                    <i class="nav-icon bi bi-people"></i>
                                    <p>Utilisateurs</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <h1 class="h3 mb-0">@yield('heading', 'Dashboard admin')</h1>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible" role="status">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    @endif

                    @error('delete')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror

                    @error('user_action')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror

                    @yield('content')
                </div>
            </div>
        </main>

        <footer class="app-footer">
            <strong>ENEAM · Bibliothèque des mémoires</strong>
        </footer>
    </div>
</body>
</html>
