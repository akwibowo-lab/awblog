@if ($paginator->hasPages())
    <nav aria-label="{{ __('blog.pagination.label') }}" class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-brand-border pt-6">
        <p class="font-mono text-xs text-brand-muted">
            {{ __('blog.pagination.page_info', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span
                    aria-disabled="true"
                    class="inline-flex min-h-11 items-center rounded-lg border border-brand-border px-4 text-sm text-brand-muted/60 cursor-not-allowed"
                >
                    {{ __('blog.pagination.previous') }}
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="inline-flex min-h-11 items-center rounded-lg border border-brand-border bg-brand-surface px-4 text-sm font-medium text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors"
                >
                    {{ __('blog.pagination.previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="inline-flex min-h-11 items-center rounded-lg border border-brand-border bg-brand-surface px-4 text-sm font-medium text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors"
                >
                    {{ __('blog.pagination.next') }}
                </a>
            @else
                <span
                    aria-disabled="true"
                    class="inline-flex min-h-11 items-center rounded-lg border border-brand-border px-4 text-sm text-brand-muted/60 cursor-not-allowed"
                >
                    {{ __('blog.pagination.next') }}
                </span>
            @endif
        </div>
    </nav>
@endif
