@php
    $liens = [
        ['route' => 'etudiant.catalogue.index', 'actif' => ['etudiant.catalogue.*'], 'icone' => 'book-open', 'texte' => 'Catalogue'],
        ['route' => 'etudiant.depots.index', 'actif' => ['etudiant.depots.index', 'etudiant.depots.show'], 'icone' => 'file-text', 'texte' => 'Mes dépôts'],
        ['route' => 'etudiant.depots.create', 'actif' => ['etudiant.depots.create'], 'icone' => 'upload', 'texte' => 'Déposer un mémoire'],
        ['route' => 'etudiant.favoris.index', 'actif' => ['etudiant.favoris.*'], 'icone' => 'heart', 'texte' => 'Favoris'],
        ['route' => 'etudiant.historique', 'actif' => ['etudiant.historique'], 'icone' => 'history', 'texte' => 'Historique'],
    ];
    $classeLien = 'flex items-center gap-3 rounded-lg px-3 text-sm font-medium transition-colors duration-200';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Bibliothèque des mémoires ENEAM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col bg-slate-50 text-slate-900 antialiased">
    <a href="#contenu" class="sr-only rounded-lg bg-white px-4 py-3 text-sm font-medium text-slate-900 focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50">
        Aller au contenu
    </a>

    <header class="relative border-b border-slate-300 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ route('etudiant.catalogue.index') }}" class="flex items-baseline gap-2 rounded-lg">
                <span class="text-sm font-medium tracking-wide text-slate-600">ENEAM</span>
                <span class="font-heading text-xl font-semibold text-[color:var(--color-primary)]">Bibliothèque des mémoires</span>
            </a>

            {{-- Navigation large écran --}}
            <nav aria-label="Navigation principale" class="hidden items-center gap-1 xl:flex">
                @foreach ($liens as $lien)
                    @php $actif = request()->routeIs(...$lien['actif']); @endphp
                    <a
                        href="{{ route($lien['route']) }}"
                        @if ($actif) aria-current="page" @endif
                        class="{{ $classeLien }} h-11 {{ $actif ? 'bg-slate-100 font-semibold text-[color:var(--color-primary)]' : 'text-slate-700 hover:bg-slate-100' }}"
                    >
                        <x-icone :name="$lien['icone']" class="size-5" />
                        {{ $lien['texte'] }}
                    </a>
                @endforeach

                {{-- Action sensible séparée de la navigation --}}
                <form method="POST" action="{{ route('logout') }}" class="ml-3 border-l border-slate-300 pl-3">
                    @csrf
                    <button type="submit" class="{{ $classeLien }} h-11 cursor-pointer text-slate-700 hover:bg-slate-100">
                        <x-icone name="log-out" class="size-5" />
                        Déconnexion
                    </button>
                </form>
            </nav>

            {{-- Menu replié sur mobile et tablette (sans JavaScript : <details>) --}}
            <details id="menu-mobile" class="group xl:hidden">
                <summary class="flex h-11 cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-500 px-3 text-sm font-medium text-slate-800 [&::-webkit-details-marker]:hidden">
                    <x-icone name="menu" class="size-5 group-open:hidden" />
                    <x-icone name="x" class="hidden size-5 group-open:block" />
                    Menu
                </summary>

                <div class="absolute inset-x-0 top-full z-20 border-b border-slate-300 bg-white shadow-sm">
                    <nav aria-label="Navigation principale mobile" class="mx-auto max-w-6xl space-y-1 p-3">
                        @foreach ($liens as $lien)
                            @php $actif = request()->routeIs(...$lien['actif']); @endphp
                            <a
                                href="{{ route($lien['route']) }}"
                                @if ($actif) aria-current="page" @endif
                                class="{{ $classeLien }} h-12 {{ $actif ? 'bg-slate-100 font-semibold text-[color:var(--color-primary)]' : 'text-slate-700 hover:bg-slate-100' }}"
                            >
                                <x-icone :name="$lien['icone']" class="size-5" />
                                {{ $lien['texte'] }}
                            </a>
                        @endforeach

                        <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-slate-300 pt-2">
                            @csrf
                            <button type="submit" class="{{ $classeLien }} h-12 w-full cursor-pointer text-slate-700 hover:bg-slate-100">
                                <x-icone name="log-out" class="size-5" />
                                Déconnexion
                            </button>
                        </form>
                    </nav>
                </div>
            </details>
        </div>
    </header>

    <main id="contenu" tabindex="-1" class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:py-10">
        @if (session('status'))
            <div class="mb-6">
                <x-alert type="success">{{ session('status') }}</x-alert>
            </div>
        @endif

        @if (session('avertissement'))
            <div role="alert" class="mb-6 flex gap-3 rounded-lg border border-amber-500 bg-amber-50 p-4 text-amber-900">
                <x-icone name="triangle-alert" class="mt-0.5 size-5" />
                <p>{{ session('avertissement') }}</p>
            </div>
        @endif

        @php($prenomBienvenue = session()->pull('etudiant_salutation'))
        @if ($prenomBienvenue)
            <p role="status" class="mb-4 text-lg font-medium text-slate-800">Salut, {{ $prenomBienvenue }} !</p>
        @endif

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="font-heading text-3xl font-semibold text-slate-900 sm:text-4xl">@yield('heading')</h1>
                @hasSection('subtitle')
                    <p class="mt-2 max-w-prose text-slate-600">@yield('subtitle')</p>
                @endif
            </div>
            @hasSection('actions')
                <div class="shrink-0">@yield('actions')</div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="border-t border-slate-300 py-6 text-center text-sm text-slate-600">
        ENEAM · Bibliothèque numérique des mémoires
    </footer>

    <script>
        // 1. Met le focus sur le premier champ en erreur
        document.querySelector('[aria-invalid="true"]')?.focus();

        // 2. Désactive le bouton pendant l'envoi pour éviter les doubles clics
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var button = form.querySelector('button[type="submit"][data-loading]');
                if (button) {
                    button.disabled = true;
                    button.textContent = button.dataset.loading;
                }
            });
        });

        // 3. Échap referme le menu mobile et rend le focus au bouton
        document.addEventListener('keydown', function (event) {
            var menu = document.getElementById('menu-mobile');
            if (event.key === 'Escape' && menu && menu.open) {
                menu.open = false;
                menu.querySelector('summary').focus();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
