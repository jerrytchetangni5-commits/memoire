@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-8 flex items-center justify-between gap-4">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="inline-flex h-11 items-center gap-2 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-500">
                <x-icone name="arrow-left" class="size-4" /> Précédent
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-11 items-center gap-2 rounded-lg border border-slate-500 bg-white px-4 text-sm font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
                <x-icone name="arrow-left" class="size-4" /> Précédent
            </a>
        @endif

        <p class="text-sm text-slate-600">Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-11 items-center gap-2 rounded-lg border border-slate-500 bg-white px-4 text-sm font-medium text-slate-800 transition-colors duration-200 hover:bg-slate-50">
                Suivant <x-icone name="arrow-right" class="size-4" />
            </a>
        @else
            <span aria-disabled="true" class="inline-flex h-11 items-center gap-2 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-500">
                Suivant <x-icone name="arrow-right" class="size-4" />
            </span>
        @endif
    </nav>
@endif
