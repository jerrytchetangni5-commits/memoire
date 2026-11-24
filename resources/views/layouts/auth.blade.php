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
<body class="bg-slate-50 text-slate-900 antialiased">
    <main class="flex min-h-dvh items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm">
            <p class="mb-8 text-center text-sm font-medium tracking-wide text-slate-600">
                ENEAM · Bibliothèque des mémoires
            </p>

            <div class="rounded-xl border border-slate-300 bg-white p-6 sm:p-8">
                <h1 class="font-heading text-3xl font-semibold text-slate-900">@yield('heading')</h1>
                @hasSection('subtitle')
                    <p class="mt-2 text-slate-600">@yield('subtitle')</p>
                @endif

                <div class="mt-6">
                    @if (session('status'))
                        <x-alert type="success">{{ session('status') }}</x-alert>
                    @endif

                    @yield('content')
                </div>
            </div>

            @hasSection('footer')
                <p class="mt-6 text-center text-sm text-slate-600">@yield('footer')</p>
            @endif
        </div>
    </main>

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
    </script>
    @stack('scripts')
</body>
</html>