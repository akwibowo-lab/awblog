@extends('layouts.public')

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <nav aria-label="{{ __('projects.show.breadcrumb_label') }}" class="flex flex-wrap items-center gap-2 text-sm text-brand-muted">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center hover:text-brand-accent transition-colors">
                {{ __('blog.nav.home') }}
            </a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-accent transition-colors">
                {{ __('blog.nav.projects') }}
            </a>
            <span aria-hidden="true">/</span>
            <span class="text-brand-text">{{ $project['title'] }}</span>
        </nav>

        <header class="mt-4 border-b border-brand-border pb-6">
            <span class="font-mono text-xs text-brand-accent">
                {{ $project['type_label'] }}
            </span>

            <h1 class="mt-3 font-display text-3xl font-semibold leading-tight tracking-tight text-brand-text sm:text-4xl">
                {{ $project['title'] }}
            </h1>

            @if ($project['summary'] !== '')
                <p class="mt-4 text-base leading-relaxed text-brand-muted sm:text-lg">
                    {{ $project['summary'] }}
                </p>
            @endif

            @if (! empty($project['links']) || $project['note'])
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @foreach ($project['links'] as $link)
                        <a
                            href="{{ $link['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            @if ($link['type'] === 'download') download @endif
                            @class([
                                'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-colors',
                                'bg-brand-accent text-brand-bg hover:opacity-90' => $loop->first,
                                'border border-brand-border bg-brand-surface font-medium text-brand-text hover:border-brand-accent hover:text-brand-accent' => ! $loop->first,
                            ])
                        >
                            @include('projects.partials.link-icon', ['type' => $link['type']])
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach

                    @if (! empty($project['note']))
                        <p class="text-sm text-brand-muted">
                            {{ $project['note'] }}
                        </p>
                    @endif
                </div>
            @endif
        </header>

        @if ($project['media'])
            <figure class="mt-8">
                <div class="aspect-video w-full overflow-hidden rounded-xl border border-brand-border bg-brand-surface">
                    <img
                        src="{{ $project['media']['large_url'] }}"
                        alt="{{ $project['media']['alt'] }}"
                        @if ($project['media']['large_width']) width="{{ $project['media']['large_width'] }}" @endif
                        @if ($project['media']['large_height']) height="{{ $project['media']['large_height'] }}" @endif
                        fetchpriority="high"
                        decoding="async"
                        class="h-full w-full object-cover"
                    >
                </div>
            </figure>
        @endif

        @if (count($project['tech_stack']) > 0)
            <section aria-labelledby="stack-heading" class="mt-8">
                <h2 id="stack-heading" class="font-display text-sm font-semibold text-brand-text">
                    {{ __('projects.show.stack_heading') }}
                </h2>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($project['tech_stack'] as $tech)
                        <li class="rounded border border-brand-border bg-brand-surface px-3 py-1.5 font-mono text-xs text-brand-text">
                            {{ $tech }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($project['description_html'] !== '')
            <section aria-labelledby="overview-heading" class="mt-8">
                <h2 id="overview-heading" class="sr-only">
                    {{ __('projects.show.overview_heading') }}
                </h2>
                <div class="article-prose">
                    {!! $project['description_html'] !!}
                </div>
            </section>
        @endif

        @if ($otherProjects->isNotEmpty())
            <section aria-labelledby="other-projects-heading" class="mt-12 border-t border-brand-border pt-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 id="other-projects-heading" class="font-display text-xl font-semibold text-brand-text">
                        {{ __('projects.show.other_heading') }}
                    </h2>
                    <a
                        href="{{ route('projects.index') }}"
                        class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-2 transition-colors hover:text-brand-accent"
                    >
                        {{ __('projects.show.back_to_index') }}
                    </a>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    @foreach ($otherProjects as $other)
                        <article class="flex flex-col rounded-xl border border-brand-border bg-brand-surface p-5 transition-colors hover:border-brand-accent/60">
                            <span class="font-mono text-xs text-brand-accent">
                                {{ $other['type_label'] }}
                            </span>
                            <h3 class="mt-2 font-display text-lg font-semibold text-brand-text">
                                <a
                                    href="{{ route('projects.show', ['slug' => $other['slug']]) }}"
                                    class="hover:text-brand-accent transition-colors"
                                >
                                    {{ $other['title'] }}
                                </a>
                            </h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-brand-muted">
                                {{ $other['summary'] }}
                            </p>
                            <div class="mt-4">
                                <a
                                    href="{{ route('projects.show', ['slug' => $other['slug']]) }}"
                                    class="inline-flex min-h-11 items-center text-sm font-semibold text-brand-accent transition-colors hover:text-brand-accent-2"
                                >
                                    {{ __('projects.index.view_detail') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @else
            <div class="mt-10 border-t border-brand-border pt-6">
                <a
                    href="{{ route('projects.index') }}"
                    class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-2 transition-colors hover:text-brand-accent"
                >
                    {{ __('projects.show.back_to_index') }}
                </a>
            </div>
        @endif
    </article>
@endsection
