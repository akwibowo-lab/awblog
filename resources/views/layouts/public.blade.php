<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $seoTitle ?? __('blog.site_name') }}</title>
        <meta name="description" content="{{ $seoDescription ?? __('blog.seo.index_description') }}">
        <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">

        <meta property="og:site_name" content="{{ __('blog.site_name') }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:title" content="{{ $seoTitle ?? __('blog.site_name') }}">
        <meta property="og:description" content="{{ $seoDescription ?? __('blog.seo.index_description') }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
        @if (! empty($ogImage))
            <meta property="og:image" content="{{ $ogImage }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:image" content="{{ $ogImage }}">
        @else
            <meta name="twitter:card" content="summary">
        @endif
        <meta name="twitter:title" content="{{ $seoTitle ?? __('blog.site_name') }}">
        <meta name="twitter:description" content="{{ $seoDescription ?? __('blog.seo.index_description') }}">

        <link rel="icon" type="image/svg+xml" href="{{ asset('branding/favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">

        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('awblog-theme');
                    var theme = stored === 'light' || stored === 'dark' ? stored : 'dark';
                    document.documentElement.setAttribute('data-theme', theme);
                } catch (e) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-bg text-brand-text font-sans antialiased flex flex-col">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-brand-accent focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-brand-bg"
        >
            {{ __('blog.skip_to_content') }}
        </a>

        <header class="sticky top-0 z-40 border-b border-brand-border bg-brand-bg/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 h-16">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex min-h-11 items-center gap-3 text-brand-text hover:text-brand-accent transition-colors"
                >
                    <img
                        src="{{ asset('branding/aw-orbit-dark.svg') }}"
                        alt="Logo Akut Wibowo"
                        width="32"
                        height="32"
                        class="h-8 w-8 shrink-0"
                    >
                    <span class="font-display text-base sm:text-lg font-semibold tracking-tight">
                        {{ __('blog.site_name') }}
                    </span>
                </a>

                <nav aria-label="{{ __('blog.nav.primary_label') }}" class="flex items-center gap-2 sm:gap-3">
                    <a
                        href="{{ route('home') }}"
                        @class([
                            'inline-flex min-h-11 items-center px-3 text-sm transition-colors border-b-2',
                            'border-brand-accent font-medium text-brand-text' => request()->routeIs('home'),
                            'border-transparent text-brand-muted hover:text-brand-text' => ! request()->routeIs('home'),
                        ])
                        @if (request()->routeIs('home')) aria-current="page" @endif
                    >
                        {{ __('blog.nav.home') }}
                    </a>
                    <a
                        href="{{ route('blog.index') }}"
                        @class([
                            'inline-flex min-h-11 items-center px-3 text-sm transition-colors border-b-2',
                            'border-brand-accent font-medium text-brand-text' => request()->routeIs('blog.*'),
                            'border-transparent text-brand-muted hover:text-brand-text' => ! request()->routeIs('blog.*'),
                        ])
                        @if (request()->routeIs('blog.index')) aria-current="page" @endif
                    >
                        {{ __('blog.nav.blog') }}
                    </a>

                    <button
                        id="theme-toggle"
                        type="button"
                        aria-pressed="false"
                        aria-label="{{ __('blog.nav.theme_to_light') }}"
                        data-label-light="{{ __('blog.nav.theme_to_light') }}"
                        data-label-dark="{{ __('blog.nav.theme_to_dark') }}"
                        class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-brand-border bg-brand-surface px-3 text-xs font-medium text-brand-text hover:border-brand-accent transition-colors"
                    >
                        <span id="theme-toggle-text">{{ __('blog.nav.theme_to_light') }}</span>
                    </button>
                </nav>
            </div>
        </header>

        <main id="main-content" class="flex-1">
            @yield('content')
        </main>

        <footer class="mt-16 border-t border-brand-border bg-brand-surface/50">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <div>
                    <p class="font-display text-sm font-semibold text-brand-text">
                        {{ __('blog.site_name') }}
                    </p>
                    <p class="mt-1 text-sm text-brand-muted">
                        {{ __('blog.footer.tagline') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm text-brand-muted">
                    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center hover:text-brand-text transition-colors">
                        {{ __('blog.nav.home') }}
                    </a>
                    <a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-text transition-colors">
                        {{ __('blog.nav.blog') }}
                    </a>
                    <span class="font-mono text-xs text-brand-muted">
                        {{ __('blog.footer.copyright', ['year' => now()->year]) }}
                    </span>
                </div>
            </div>
        </footer>

        <script>
            (function () {
                var btn = document.getElementById('theme-toggle');
                var label = document.getElementById('theme-toggle-text');
                if (!btn || !label) return;

                function sync(theme) {
                    var isLight = theme === 'light';
                    var nextText = isLight ? btn.dataset.labelDark : btn.dataset.labelLight;
                    btn.setAttribute('aria-pressed', isLight ? 'true' : 'false');
                    btn.setAttribute('aria-label', nextText);
                    label.textContent = nextText;
                }

                sync(document.documentElement.getAttribute('data-theme') || 'dark');

                btn.addEventListener('click', function () {
                    var current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
                    var next = current === 'light' ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', next);
                    try {
                        localStorage.setItem('awblog-theme', next);
                    } catch (e) {}
                    sync(next);
                });
            })();
        </script>
    </body>
</html>
