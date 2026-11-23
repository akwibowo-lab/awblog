@extends('layouts.public')

@section('content')
    @php
        $featuredMedia = $post->featuredMedia;
        $featuredDims = $featuredMedia?->variantDimensions('large') ?? ['width' => null, 'height' => null];
        $primaryCategory = $post->categories->first();
    @endphp

    <article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <nav aria-label="{{ __('blog.show.breadcrumb_label') }}" class="flex flex-wrap items-center gap-2 text-sm text-brand-muted">
            <a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-accent transition-colors">
                {{ __('blog.nav.blog') }}
            </a>
            @if ($primaryCategory)
                <span aria-hidden="true">/</span>
                <a
                    href="{{ route('blog.category', ['category' => $primaryCategory->slug]) }}"
                    class="inline-flex min-h-11 items-center hover:text-brand-accent transition-colors"
                >
                    {{ $primaryCategory->name }}
                </a>
            @endif
        </nav>

        <header class="mt-4">
            @if ($post->categories->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($post->categories as $category)
                        <a
                            href="{{ route('blog.category', ['category' => $category->slug]) }}"
                            class="inline-flex min-h-11 items-center rounded-lg border border-brand-border bg-brand-surface px-3 font-mono text-xs font-medium text-brand-accent hover:border-brand-accent transition-colors"
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <h1 class="mt-4 font-display text-3xl sm:text-4xl lg:text-[2.625rem] font-semibold leading-tight tracking-tight text-brand-text">
                {{ $post->title }}
            </h1>

            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-brand-border pb-6 text-sm text-brand-muted">
                @if ($post->author)
                    <span>{{ __('blog.show.by_author', ['name' => $post->author->name]) }}</span>
                @endif

                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                        {{ __('blog.show.published_on', ['date' => $post->published_at->translatedFormat('d F Y')]) }}
                    </time>
                @endif

                @if ($post->reading_time_minutes)
                    <span class="font-mono text-xs text-brand-accent-2">
                        {{ __('blog.show.reading_time', ['minutes' => $post->reading_time_minutes]) }}
                    </span>
                @endif
            </div>
        </header>

        @if ($featuredMedia)
            <figure class="mt-8">
                <div class="overflow-hidden rounded-xl border border-brand-border bg-brand-surface">
                    <img
                        src="{{ $featuredMedia->variantUrl('large') }}"
                        alt="{{ $featuredMedia->altText($post->title) }}"
                        @if ($featuredDims['width']) width="{{ $featuredDims['width'] }}" @endif
                        @if ($featuredDims['height']) height="{{ $featuredDims['height'] }}" @endif
                        fetchpriority="high"
                        decoding="async"
                        class="h-auto w-full object-cover"
                    >
                </div>
                @if (trim((string) $featuredMedia->caption) !== '')
                    <figcaption class="mt-2.5 text-sm text-brand-muted">
                        {{ $featuredMedia->caption }}
                    </figcaption>
                @endif
            </figure>
        @endif

        <div class="article-prose mt-8">
            {!! $post->renderedContentHtml() !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <section aria-labelledby="post-tags-heading" class="mt-10 border-t border-brand-border pt-6">
                <h2 id="post-tags-heading" class="font-display text-sm font-semibold text-brand-muted">
                    {{ __('blog.show.tags_heading') }}
                </h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <a
                            href="{{ route('blog.tag', ['tag' => $tag->slug]) }}"
                            class="inline-flex min-h-11 items-center rounded-lg border border-brand-border bg-brand-surface px-3.5 text-sm text-brand-text hover:border-brand-accent-2 hover:text-brand-accent-2 transition-colors"
                        >
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-10 border-t border-brand-border pt-6">
            <a
                href="{{ route('blog.index') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-brand-border bg-brand-surface px-4 text-sm font-medium text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors"
            >
                {{ __('blog.show.back_to_blog') }}
            </a>
        </div>
    </article>

    @if ($relatedPosts->isNotEmpty())
        <section aria-labelledby="related-posts-heading" class="mx-auto max-w-6xl border-t border-brand-border px-4 py-12 sm:px-6 lg:px-8">
            <h2 id="related-posts-heading" class="font-display text-2xl font-semibold text-brand-text">
                {{ __('blog.show.related_heading') }}
            </h2>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedPosts as $relatedPost)
                    @include('blog.partials.post-card', ['post' => $relatedPost])
                @endforeach
            </div>
        </section>
    @endif
@endsection
