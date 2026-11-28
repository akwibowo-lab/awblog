<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $seoTitle ?? __('blog.site_name') }}</title>
        <meta name="description" content="{{ $seoDescription ?? __('blog.seo.index_description') }}">
        <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
        <link rel="alternate" type="application/rss+xml" title="{{ __('blog.seo.rss_title') }}" href="{{ route('seo.feed') }}">

        <meta property="og:site_name" content="{{ __('blog.site_name') }}">
        <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'id_ID' }}">
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
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-2 sm:gap-4 px-4 sm:px-6 lg:px-8 h-16">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex min-h-11 min-w-0 items-center gap-2 sm:gap-3 text-brand-text hover:text-brand-accent transition-colors"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="-60 -60 120 120"
                        width="32"
                        height="32"
                        aria-hidden="true"
                        class="h-7 w-7 sm:h-8 sm:w-8 shrink-0"
                    >
                        <path d="M47.2 -26.2 A54 54 0 1 1 26.2 -47.2" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="38.2" cy="-38.2" r="7" fill="var(--color-brand-accent)"/>
                        <path d="M-35.5 18 L-20.5 -18 L-5.5 18 M-30.5 6 L-10.5 6" fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="miter"/>
                        <path d="M-0.5 -18 L8.5 18 L17.5 -6.4 L26.5 18 L35.5 -18" fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="miter"/>
                    </svg>
                    <span class="truncate font-display text-sm sm:text-lg font-semibold tracking-tight">
                        {{ __('blog.site_name') }}
                    </span>
                </a>

                <nav aria-label="{{ __('blog.nav.primary_label') }}" class="flex shrink-0 items-center gap-0.5 sm:gap-3">
                    <a
                        href="{{ route('home') }}"
                        @class([
                            'inline-flex min-h-11 items-center px-1.5 sm:px-3 text-xs sm:text-sm transition-colors border-b-2',
                            'border-brand-accent font-medium text-brand-text' => request()->routeIs('home'),
                            'border-transparent text-brand-muted hover:text-brand-text' => ! request()->routeIs('home'),
                        ])
                        @if (request()->routeIs('home')) aria-current="page" @endif
                    >
                        {{ __('blog.nav.home') }}
                    </a>
                    <a
                        href="{{ route('projects.index') }}"
                        @class([
                            'inline-flex min-h-11 items-center px-1.5 sm:px-3 text-xs sm:text-sm transition-colors border-b-2',
                            'border-brand-accent font-medium text-brand-text' => request()->routeIs('projects.*'),
                            'border-transparent text-brand-muted hover:text-brand-text' => ! request()->routeIs('projects.*'),
                        ])
                        @if (request()->routeIs('projects.index')) aria-current="page" @endif
                    >
                        {{ __('blog.nav.projects') }}
                    </a>
                    <a
                        href="{{ route('blog.index') }}"
                        @class([
                            'inline-flex min-h-11 items-center px-1.5 sm:px-3 text-xs sm:text-sm transition-colors border-b-2',
                            'border-brand-accent font-medium text-brand-text' => request()->routeIs('blog.*'),
                            'border-transparent text-brand-muted hover:text-brand-text' => ! request()->routeIs('blog.*'),
                        ])
                        @if (request()->routeIs('blog.index')) aria-current="page" @endif
                    >
                        {{ __('blog.nav.blog') }}
                    </a>

                    @if (request()->routeIs('home', 'projects.index', 'projects.show'))
                        @php
                            $currentLocale = app()->getLocale();
                            $nextLocale = $currentLocale === 'en' ? 'id' : 'en';
                            $langLabel = $nextLocale === 'en' ? __('blog.nav.lang_to_en') : __('blog.nav.lang_to_id');
                        @endphp
                        <a
                            id="lang-toggle"
                            href="{{ route('locale.switch', ['locale' => $nextLocale]) }}"
                            aria-label="{{ $langLabel }}"
                            title="{{ $langLabel }}"
                            data-locale="{{ $currentLocale }}"
                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface px-2 font-mono text-xs font-semibold uppercase tracking-wide text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors"
                        >
                            {{ strtoupper($nextLocale) }}
                        </a>
                    @endif

                    <button
                        id="theme-toggle"
                        type="button"
                        aria-pressed="false"
                        aria-label="{{ __('blog.nav.theme_to_light') }}"
                        title="{{ __('blog.nav.theme_to_light') }}"
                        data-label-light="{{ __('blog.nav.theme_to_light') }}"
                        data-label-dark="{{ __('blog.nav.theme_to_dark') }}"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors"
                    >
                        <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                        </svg>
                        <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="hidden">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
                        </svg>
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
                    <a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-text transition-colors">
                        {{ __('blog.nav.projects') }}
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
                var sun = document.getElementById('theme-icon-sun');
                var moon = document.getElementById('theme-icon-moon');
                if (!btn || !sun || !moon) return;

                function sync(theme) {
                    var isLight = theme === 'light';
                    var nextText = isLight ? btn.dataset.labelDark : btn.dataset.labelLight;
                    btn.setAttribute('aria-pressed', isLight ? 'true' : 'false');
                    btn.setAttribute('aria-label', nextText);
                    btn.setAttribute('title', nextText);
                    sun.classList.toggle('hidden', isLight);
                    moon.classList.toggle('hidden', !isLight);
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
