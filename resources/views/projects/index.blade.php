@extends('layouts.public')

@section('content')
    <section class="hero-stage border-b border-brand-border">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <p class="font-mono text-xs tracking-wide text-brand-accent sm:text-sm">
                {{ __('projects.index.eyebrow') }}
            </p>
            <h1 class="hero-title mt-3 font-display font-semibold text-brand-text">
                {{ __('projects.index.heading') }}
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-brand-muted sm:text-lg">
                {{ __('projects.index.intro') }}
            </p>

            <nav aria-label="{{ __('projects.index.filter_label') }}" class="mt-7 flex flex-wrap gap-2.5">
                <a
                    href="{{ route('projects.index') }}"
                    @class([
                        'inline-flex min-h-11 items-center rounded-lg border px-4 py-2 text-sm font-medium transition-colors',
                        'border-brand-accent bg-brand-accent text-brand-bg' => $activeType === null,
                        'border-brand-border bg-brand-surface text-brand-text hover:border-brand-accent hover:text-brand-accent' => $activeType !== null,
                    ])
                    @if ($activeType === null) aria-current="page" @endif
                >
                    {{ __('projects.index.filter_all') }}
                </a>

                @foreach ($types as $type)
                    @php
                        $isSelected = $activeType === $type->value;
                    @endphp
                    <a
                        href="{{ route('projects.index', ['type' => $type->value]) }}"
                        @class([
                            'inline-flex min-h-11 items-center rounded-lg border px-4 py-2 text-sm font-medium transition-colors',
                            'border-brand-accent bg-brand-accent text-brand-bg' => $isSelected,
                            'border-brand-border bg-brand-surface text-brand-text hover:border-brand-accent hover:text-brand-accent' => ! $isSelected,
                        ])
                        @if ($isSelected) aria-current="page" @endif
                    >
                        {{ __('projects.types.'.$type->value) }}
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="section-band">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($projects->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($projects as $project)
                        @php
                            $isLead = $loop->first && $project['is_featured'] && $projects->count() > 1;
                            $media = $project['media'];
                        @endphp

                        <article @class([
                            'flex flex-col overflow-hidden rounded-xl border border-brand-border bg-brand-surface transition-colors hover:border-brand-accent/60',
                            'md:col-span-2 lg:grid lg:grid-cols-12' => $isLead,
                        ])>
                            @if ($media)
                                <a
                                    href="{{ route('projects.show', ['slug' => $project['slug']]) }}"
                                    @class([
                                        'block overflow-hidden bg-brand-bg',
                                        'aspect-video w-full' => ! $isLead,
                                        'aspect-video w-full lg:col-span-6 lg:self-center' => $isLead,
                                    ])
                                >
                                    <img
                                        src="{{ $media['url'] }}"
                                        alt="{{ $media['alt'] }}"
                                        @if ($media['width']) width="{{ $media['width'] }}" @endif
                                        @if ($media['height']) height="{{ $media['height'] }}" @endif
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-cover"
                                    >
                                </a>
                            @endif

                            <div @class([
                                'flex flex-1 flex-col p-5 sm:p-7',
                                'lg:col-span-6' => $isLead && $media,
                                'lg:col-span-12' => $isLead && ! $media,
                            ])>
                                <span class="font-mono text-xs text-brand-accent">
                                    {{ $project['type_label'] }}
                                </span>

                                <h2 class="mt-2 font-display text-xl font-semibold text-brand-text sm:text-2xl">
                                    <a
                                        href="{{ route('projects.show', ['slug' => $project['slug']]) }}"
                                        class="hover:text-brand-accent transition-colors"
                                    >
                                        {{ $project['title'] }}
                                    </a>
                                </h2>

                                <p class="mt-3 flex-1 text-sm leading-relaxed text-brand-muted sm:text-base">
                                    {{ $project['summary'] }}
                                </p>

                                @if (count($project['tech_stack']) > 0)
                                    <ul class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($project['tech_stack'] as $tech)
                                            <li class="rounded border border-brand-border bg-brand-bg px-2.5 py-1 font-mono text-xs text-brand-muted">
                                                {{ $tech }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if (! empty($project['note']))
                                    <p class="mt-4 text-xs text-brand-muted">
                                        {{ $project['note'] }}
                                    </p>
                                @endif

                                <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-brand-border/70 pt-3">
                                    <a
                                        href="{{ route('projects.show', ['slug' => $project['slug']]) }}"
                                        class="inline-flex min-h-11 items-center text-sm font-semibold text-brand-accent transition-colors hover:text-brand-accent-2"
                                    >
                                        {{ __('projects.index.view_detail') }}
                                    </a>

                                    @if ($project['live_url'])
                                        <a
                                            href="{{ $project['live_url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-2 transition-colors hover:text-brand-accent"
                                        >
                                            {{ $project['live_label'] ?? __('projects.show.live_link') }}
                                        </a>
                                    @endif

                                    @if ($project['repo_url'])
                                        <a
                                            href="{{ $project['repo_url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex min-h-11 items-center text-sm font-medium text-brand-muted transition-colors hover:text-brand-text"
                                        >
                                            {{ __('projects.show.repo_link') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-brand-border bg-brand-surface p-6 sm:p-8">
                    <h2 class="font-display text-lg font-semibold text-brand-text sm:text-xl">
                        {{ __('projects.index.empty_title') }}
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-brand-muted">
                        {{ __('projects.index.empty_body') }}
                    </p>
                    <div class="mt-5">
                        <a
                            href="{{ route('projects.index') }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-accent px-4 py-2 text-sm font-semibold text-brand-bg transition-opacity hover:opacity-90"
                        >
                            {{ __('projects.index.empty_reset') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section aria-labelledby="projects-cta-heading" class="border-t border-brand-border bg-brand-surface/40 py-12 sm:py-16">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-12 lg:items-center lg:px-8">
            <div class="min-w-0 lg:col-span-7">
                <h2 id="projects-cta-heading" class="section-title font-display font-semibold tracking-tight text-brand-text">
                    {{ __('projects.index.cta_heading') }}
                </h2>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-brand-muted sm:text-base">
                    {{ __('projects.index.cta_body') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 sm:gap-4 lg:col-span-5 lg:justify-end">
                <a
                    href="{{ __('home.contact.whatsapp_url') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-accent px-5 py-2.5 text-sm font-semibold text-brand-bg transition-opacity hover:opacity-90"
                >
                    {{ __('projects.index.cta_whatsapp') }}
                </a>

                <a
                    href="mailto:{{ __('home.contact.email_address') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-border bg-brand-surface px-5 py-2.5 text-sm font-medium text-brand-text transition-colors hover:border-brand-accent hover:text-brand-accent"
                >
                    {{ __('projects.index.cta_email') }}
                </a>
            </div>
        </div>
    </section>
@endsection
