@extends('layouts.public')

@section('content')
    {{-- Hero --}}
    <section aria-labelledby="hero-heading" class="hero-stage border-b border-brand-border">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-24">
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-12">
                <div class="min-w-0 lg:col-span-8">
                    <p class="font-mono text-xs tracking-wide text-brand-accent sm:text-sm">
                        {{ __('home.hero.role') }}
                    </p>

                    <h1 id="hero-heading" class="hero-title mt-3 font-display font-semibold text-brand-text">
                        {{ __('home.hero.name') }}
                    </h1>

                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-brand-muted sm:text-lg">
                        {{ __('home.hero.summary') }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a
                            href="#aplikasi"
                            class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-accent px-5 py-2.5 text-sm font-semibold text-brand-bg transition-opacity hover:opacity-90"
                        >
                            {{ __('home.hero.cta_projects') }}
                        </a>

                        <a
                            href="{{ __('home.hero.whatsapp_url') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface px-5 py-2.5 text-sm font-medium text-brand-text transition-colors hover:border-brand-accent hover:text-brand-accent"
                        >
                            {{ __('home.hero.cta_contact') }}
                        </a>
                    </div>
                </div>

                <div class="flex justify-start lg:col-span-4 lg:justify-end">
                    <img
                        src="{{ asset('branding/aw-orbit-dark.svg') }}"
                        alt="{{ __('home.hero.orbit_alt') }}"
                        width="220"
                        height="220"
                        fetchpriority="high"
                        decoding="sync"
                        class="h-36 w-36 sm:h-48 sm:w-48 lg:h-52 lg:w-52"
                    >
                </div>
            </div>
        </div>
    </section>

    {{-- Sertifikasi Drone --}}
    <section id="sertifikasi" aria-labelledby="certification-heading" class="section-band scroll-mt-20">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:gap-14 lg:px-8">
            <div class="min-w-0 lg:col-span-4">
                <h2 id="certification-heading" class="section-title font-display font-semibold tracking-tight text-brand-text">
                    {{ __('home.certification.heading') }}
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-brand-muted sm:text-base">
                    {{ __('home.certification.intro') }}
                </p>

                @if ($portfolioPdfUrl)
                    <div class="mt-6">
                        <a
                            href="{{ $portfolioPdfUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface px-4 py-2.5 text-sm font-medium text-brand-text transition-colors hover:border-brand-accent hover:text-brand-accent"
                        >
                            {{ __('home.certification.download_pdf') }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="min-w-0 lg:col-span-8">
                <ol class="divide-y divide-brand-border border-y border-brand-border">
                    @foreach (__('home.certification.items') as $item)
                        <li class="grid gap-2 py-5 sm:grid-cols-12 sm:items-baseline sm:gap-6 sm:py-6">
                            <span class="font-mono text-xs font-medium text-brand-accent sm:col-span-3">
                                {{ $item['code'] }}
                            </span>
                            <div class="min-w-0 sm:col-span-9">
                                <h3 class="font-display text-base font-semibold text-brand-text sm:text-lg">
                                    {{ $item['title'] }}
                                </h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-brand-muted">
                                    {{ $item['detail'] }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- Galeri Aplikasi --}}
    <section id="aplikasi" aria-labelledby="projects-heading" class="section-band scroll-mt-20 border-y border-brand-border bg-brand-surface/40">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <h2 id="projects-heading" class="section-title font-display font-semibold tracking-tight text-brand-text">
                    {{ __('home.projects.heading') }}
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-brand-muted sm:text-base">
                    {{ __('home.projects.intro') }}
                </p>
            </div>

            @if ($projects->isNotEmpty())
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @foreach ($projects as $project)
                        @php
                            $media = $project->featuredMedia;
                            $dimensions = $media?->variantDimensions('medium') ?? ['width' => null, 'height' => null];
                            $typeLabel = __('home.projects.types.'.$project->type->value);
                            $isLead = $loop->first && $project->is_featured;
                        @endphp

                        <article @class([
                            'flex flex-col overflow-hidden rounded-xl border border-brand-border bg-brand-bg transition-colors hover:border-brand-accent/60',
                            'md:col-span-2 lg:grid lg:grid-cols-12' => $isLead,
                        ])>
                            @if ($media)
                                <div @class([
                                    'overflow-hidden bg-brand-surface',
                                    'aspect-[16/10] w-full' => ! $isLead,
                                    'aspect-[16/10] lg:aspect-auto lg:col-span-6 lg:h-full' => $isLead,
                                ])>
                                    <img
                                        src="{{ $media->variantUrl('medium') }}"
                                        alt="{{ $media->altText($project->title_id) }}"
                                        @if ($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                        @if ($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                            @endif

                            <div @class([
                                'flex flex-1 flex-col p-5 sm:p-6',
                                'lg:col-span-6' => $isLead && $media,
                                'lg:col-span-12' => $isLead && ! $media,
                            ])>
                                <span class="font-mono text-xs text-brand-accent">
                                    {{ $typeLabel }}
                                </span>

                                <h3 class="mt-2 font-display text-lg font-semibold text-brand-text sm:text-xl">
                                    {{ $project->title_id }}
                                </h3>

                                <p class="mt-2.5 flex-1 text-sm leading-relaxed text-brand-muted">
                                    {{ $project->summary_id }}
                                </p>

                                @if (is_array($project->tech_stack) && count($project->tech_stack) > 0)
                                    <ul class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($project->tech_stack as $tech)
                                            <li class="rounded border border-brand-border bg-brand-surface px-2.5 py-1 font-mono text-xs text-brand-muted">
                                                {{ $tech }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($project->live_url || $project->repo_url)
                                    <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-brand-border/70 pt-3">
                                        @if ($project->live_url)
                                            <a
                                                href="{{ $project->live_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-2 transition-colors hover:text-brand-accent"
                                            >
                                                {{ __('home.projects.live_link') }}
                                            </a>
                                        @endif

                                        @if ($project->repo_url)
                                            <a
                                                href="{{ $project->repo_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex min-h-11 items-center text-sm font-medium text-brand-muted transition-colors hover:text-brand-text"
                                            >
                                                {{ __('home.projects.repo_link') }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @foreach (__('home.projects.fallback_items') as $item)
                        <article class="flex flex-col rounded-xl border border-brand-border bg-brand-bg p-5 sm:p-6">
                            <span class="font-mono text-xs text-brand-accent">
                                {{ $item['type'] }}
                            </span>
                            <h3 class="mt-2 font-display text-lg font-semibold text-brand-text sm:text-xl">
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-2.5 flex-1 text-sm leading-relaxed text-brand-muted">
                                {{ $item['summary'] }}
                            </p>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($item['stack'] as $tech)
                                    <li class="rounded border border-brand-border bg-brand-surface px-2.5 py-1 font-mono text-xs text-brand-muted">
                                        {{ $tech }}
                                    </li>
                                @endforeach
                            </ul>

                            @if (! empty($item['url']))
                                <div class="mt-5 border-t border-brand-border/70 pt-4">
                                    <a
                                        href="{{ $item['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface px-4 py-2 text-sm font-medium text-brand-text transition-colors hover:border-brand-accent hover:text-brand-accent"
                                    >
                                        {{ $item['url_label'] }}
                                    </a>
                                </div>
                            @elseif (! empty($item['note']))
                                <p class="mt-5 border-t border-brand-border/70 pt-3 text-xs text-brand-muted">
                                    {{ $item['note'] }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Blog Terbaru --}}
    <section id="blog" aria-labelledby="blog-heading" class="section-band scroll-mt-20 border-b border-brand-border">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <h2 id="blog-heading" class="section-title font-display font-semibold tracking-tight text-brand-text">
                        {{ __('home.blog.heading') }}
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-brand-muted sm:text-base">
                        {{ __('home.blog.intro') }}
                    </p>
                </div>

                <a
                    href="{{ route('blog.index') }}"
                    class="inline-flex min-h-11 shrink-0 items-center text-sm font-medium text-brand-accent-2 transition-colors hover:text-brand-accent"
                >
                    {{ __('home.blog.view_all') }}
                </a>
            </div>

            @if ($posts->isNotEmpty())
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        @include('blog.partials.post-card', ['post' => $post])
                    @endforeach
                </div>
            @else
                <p class="mt-8 rounded-xl border border-brand-border bg-brand-surface p-6 text-sm text-brand-muted">
                    {{ __('home.blog.empty') }}
                </p>
            @endif
        </div>
    </section>

    {{-- Kontak --}}
    <section id="kontak" aria-labelledby="contact-heading" class="section-band scroll-mt-20">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-12 lg:items-center lg:gap-12 lg:px-8">
            <div class="min-w-0 lg:col-span-7">
                <h2 id="contact-heading" class="section-title font-display font-semibold tracking-tight text-brand-text">
                    {{ __('home.contact.heading') }}
                </h2>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-brand-muted sm:text-base">
                    {{ __('home.contact.body') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 sm:gap-4 lg:col-span-5 lg:justify-end">
                <a
                    href="{{ __('home.contact.whatsapp_url') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-accent px-5 py-2.5 text-sm font-semibold text-brand-bg transition-opacity hover:opacity-90"
                >
                    {{ __('home.contact.whatsapp_label') }}
                </a>

                <a
                    href="mailto:{{ __('home.contact.email_address') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface px-5 py-2.5 text-sm font-medium text-brand-text transition-colors hover:border-brand-accent hover:text-brand-accent"
                >
                    {{ __('home.contact.email_label') }}
                </a>

                <a
                    href="{{ route('seo.feed') }}"
                    class="inline-flex min-h-11 items-center px-3 text-sm font-medium text-brand-muted transition-colors hover:text-brand-text"
                >
                    {{ __('home.contact.rss_label') }}
                </a>
            </div>
        </div>
    </section>

    <button
        id="back-to-top"
        type="button"
        aria-label="{{ __('home.back_to_top') }}"
        title="{{ __('home.back_to_top') }}"
        class="fixed bottom-5 right-5 z-30 hidden min-h-11 min-w-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface text-brand-text shadow-lg transition-colors hover:border-brand-accent hover:text-brand-accent"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 19V5M5 12l7-7 7 7"/>
        </svg>
    </button>

    <script>
        (function () {
            var button = document.getElementById('back-to-top');
            if (!button) return;

            var toggleVisibility = function () {
                if (window.scrollY > 320) {
                    button.classList.remove('hidden');
                    button.classList.add('inline-flex');
                } else {
                    button.classList.add('hidden');
                    button.classList.remove('inline-flex');
                }
            };

            window.addEventListener('scroll', toggleVisibility, { passive: true });
            toggleVisibility();

            button.addEventListener('click', function () {
                var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
            });
        })();
    </script>
@endsection
