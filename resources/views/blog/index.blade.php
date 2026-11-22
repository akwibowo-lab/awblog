@extends('layouts.public')

@section('content')
    @php
        $formAction = $activeCategory
            ? route('blog.category', ['category' => $activeCategory->slug])
            : ($activeTag
                ? route('blog.tag', ['tag' => $activeTag->slug])
                : route('blog.index'));

        $showLeadPost = $posts->currentPage() === 1
            && $searchQuery === ''
            && ! $activeCategory
            && ! $activeTag
            && $posts->count() > 0;

        $leadPost = $showLeadPost ? $posts->first() : null;
        $gridPosts = $showLeadPost ? $posts->slice(1) : $posts;
    @endphp

    <section class="border-b border-brand-border bg-brand-surface/35">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-7">
                    <h1 class="font-display text-3xl sm:text-4xl font-semibold tracking-tight text-brand-text">
                        @if ($activeCategory)
                            {{ __('blog.index.category_heading', ['name' => $activeCategory->name]) }}
                        @elseif ($activeTag)
                            {{ __('blog.index.tag_heading', ['name' => $activeTag->name]) }}
                        @else
                            {{ __('blog.index.heading') }}
                        @endif
                    </h1>

                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-brand-muted">
                        @if ($activeCategory && trim((string) $activeCategory->description) !== '')
                            {{ $activeCategory->description }}
                        @elseif ($activeTag && trim((string) $activeTag->description) !== '')
                            {{ $activeTag->description }}
                        @else
                            {{ __('blog.index.subtitle') }}
                        @endif
                    </p>

                    <p class="mt-3 font-mono text-xs text-brand-muted">
                        @if ($searchQuery !== '')
                            {{ __('blog.index.search_results_summary', ['count' => $totalPosts ?? $posts->total(), 'query' => $searchQuery]) }}
                        @else
                            {{ __('blog.index.total_posts', ['count' => $totalPosts ?? $posts->total()]) }}
                        @endif
                    </p>
                </div>

                <div class="lg:col-span-5">
                    <form action="{{ $formAction }}" method="GET" role="search" class="space-y-2">
                        <label for="blog-search" class="block text-sm font-medium text-brand-text">
                            {{ __('blog.index.search_label') }}
                        </label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input
                                id="blog-search"
                                name="q"
                                type="search"
                                value="{{ $searchQuery }}"
                                maxlength="120"
                                placeholder="{{ __('blog.index.search_placeholder') }}"
                                class="min-h-11 w-full flex-1 rounded-lg border border-brand-border bg-brand-bg px-3.5 text-sm text-brand-text placeholder:text-brand-muted focus:border-brand-accent"
                            >
                            <button
                                type="submit"
                                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-lg bg-brand-accent px-5 text-sm font-semibold text-brand-bg hover:bg-brand-accent/90 active:bg-brand-accent/80 transition-colors"
                            >
                                {{ __('blog.index.search_submit') }}
                            </button>
                        </div>

                        @error('q')
                            <p role="alert" class="text-sm text-brand-accent">
                                {{ $message }}
                            </p>
                        @enderror

                        @if ($searchQuery !== '')
                            <div>
                                <a
                                    href="{{ $formAction }}"
                                    class="inline-flex min-h-11 items-center text-sm text-brand-accent-2 hover:text-brand-accent transition-colors"
                                >
                                    {{ __('blog.index.search_reset') }}
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            @if ($categories->isNotEmpty())
                <nav aria-label="{{ __('blog.index.categories_label') }}" class="mt-8 flex flex-wrap gap-2">
                    <a
                        href="{{ route('blog.index') }}"
                        @class([
                            'inline-flex min-h-11 items-center rounded-lg border px-3.5 text-sm transition-colors',
                            'border-brand-accent bg-brand-accent text-brand-bg font-semibold' => ! $activeCategory && ! $activeTag,
                            'border-brand-border bg-brand-bg text-brand-text hover:border-brand-accent/70' => $activeCategory || $activeTag,
                        ])
                        @if (! $activeCategory && ! $activeTag) aria-current="page" @endif
                    >
                        {{ __('blog.index.all_categories') }}
                    </a>

                    @foreach ($categories as $category)
                        @php
                            $isActiveCategory = $activeCategory && $activeCategory->is($category);
                        @endphp
                        <a
                            href="{{ route('blog.category', ['category' => $category->slug]) }}"
                            @class([
                                'inline-flex min-h-11 items-center gap-2 rounded-lg border px-3.5 text-sm transition-colors',
                                'border-brand-accent bg-brand-accent text-brand-bg font-semibold' => $isActiveCategory,
                                'border-brand-border bg-brand-bg text-brand-text hover:border-brand-accent/70' => ! $isActiveCategory,
                            ])
                            @if ($isActiveCategory) aria-current="page" @endif
                        >
                            <span>{{ $category->name }}</span>
                            <span @class([
                                'font-mono text-xs',
                                'text-brand-bg/80' => $isActiveCategory,
                                'text-brand-muted' => ! $isActiveCategory,
                            ])>
                                {{ $category->posts_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($posts->isEmpty())
            <div class="rounded-xl border border-brand-border bg-brand-surface p-8 sm:p-10 text-center">
                <h2 class="font-display text-xl font-semibold text-brand-text">
                    @if ($searchQuery !== '')
                        {{ __('blog.index.empty_search_title', ['query' => $searchQuery]) }}
                    @else
                        {{ __('blog.index.empty_archive_title') }}
                    @endif
                </h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-brand-muted">
                    @if ($searchQuery !== '')
                        {{ __('blog.index.empty_search_body') }}
                    @else
                        {{ __('blog.index.empty_archive_body') }}
                    @endif
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('blog.index') }}"
                        class="inline-flex min-h-11 items-center rounded-lg bg-brand-accent px-5 text-sm font-semibold text-brand-bg hover:bg-brand-accent/90 transition-colors"
                    >
                        {{ __('blog.index.reset_filter') }}
                    </a>
                </div>
            </div>
        @else
            @if ($leadPost)
                @php
                    $leadMedia = $leadPost->featuredMedia;
                    $leadDims = $leadMedia?->variantDimensions('large') ?? ['width' => null, 'height' => null];
                    $leadCategory = $leadPost->categories->first();
                @endphp

                <article class="mb-10 overflow-hidden rounded-xl border border-brand-border bg-brand-surface transition-colors hover:border-brand-accent/60">
                    <div class="grid lg:grid-cols-12">
                        @if ($leadMedia)
                            <a href="{{ $leadPost->publicUrl() }}" class="block aspect-[16/10] lg:aspect-auto lg:col-span-7 overflow-hidden bg-brand-bg">
                                <img
                                    src="{{ $leadMedia->variantUrl('large') }}"
                                    alt="{{ $leadMedia->altText($leadPost->title) }}"
                                    @if ($leadDims['width']) width="{{ $leadDims['width'] }}" @endif
                                    @if ($leadDims['height']) height="{{ $leadDims['height'] }}" @endif
                                    fetchpriority="high"
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                >
                            </a>
                        @endif

                        <div @class([
                            'flex flex-col justify-center p-6 sm:p-8',
                            'lg:col-span-5' => (bool) $leadMedia,
                            'lg:col-span-12' => ! $leadMedia,
                        ])>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-brand-muted">
                                <span class="font-mono text-brand-accent-2">
                                    {{ __('blog.index.featured_label') }}
                                </span>
                                @if ($leadCategory)
                                    <span aria-hidden="true">/</span>
                                    <a
                                        href="{{ route('blog.category', ['category' => $leadCategory->slug]) }}"
                                        class="font-mono font-medium text-brand-accent hover:underline"
                                    >
                                        {{ $leadCategory->name }}
                                    </a>
                                @endif
                                @if ($leadPost->published_at)
                                    <span aria-hidden="true">/</span>
                                    <time datetime="{{ $leadPost->published_at->toIso8601String() }}">
                                        {{ $leadPost->published_at->translatedFormat('d F Y') }}
                                    </time>
                                @endif
                            </div>

                            <h2 class="mt-3 font-display text-2xl sm:text-3xl font-semibold leading-tight text-brand-text">
                                <a href="{{ $leadPost->publicUrl() }}" class="hover:text-brand-accent transition-colors">
                                    {{ $leadPost->title }}
                                </a>
                            </h2>

                            <p class="mt-3 text-base leading-relaxed text-brand-muted">
                                {{ $leadPost->displayExcerpt(220) }}
                            </p>

                            <div class="mt-6">
                                <a
                                    href="{{ $leadPost->publicUrl() }}"
                                    class="inline-flex min-h-11 items-center rounded-lg border border-brand-border bg-brand-bg px-4 text-sm font-medium text-brand-text hover:border-brand-accent hover:text-brand-accent transition-colors"
                                >
                                    {{ __('blog.index.read_article') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @endif

            @if ($gridPosts->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($gridPosts as $post)
                        @include('blog.partials.post-card', ['post' => $post])
                    @endforeach
                </div>
            @endif

            @include('blog.partials.pagination', ['paginator' => $posts])
        @endif

        @if ($tags->isNotEmpty())
            <aside aria-labelledby="popular-tags-heading" class="mt-14 border-t border-brand-border pt-8">
                <h2 id="popular-tags-heading" class="font-display text-lg font-semibold text-brand-text">
                    {{ __('blog.index.popular_tags_heading') }}
                </h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        @php
                            $isActiveTag = $activeTag && $activeTag->is($tag);
                        @endphp
                        <a
                            href="{{ route('blog.tag', ['tag' => $tag->slug]) }}"
                            @class([
                                'inline-flex min-h-11 items-center gap-2 rounded-lg border px-3 text-sm transition-colors',
                                'border-brand-accent bg-brand-accent text-brand-bg font-semibold' => $isActiveTag,
                                'border-brand-border bg-brand-surface text-brand-text hover:border-brand-accent-2 hover:text-brand-accent-2' => ! $isActiveTag,
                            ])
                            @if ($isActiveTag) aria-current="page" @endif
                        >
                            <span>#{{ $tag->name }}</span>
                            <span @class([
                                'font-mono text-xs',
                                'text-brand-bg/80' => $isActiveTag,
                                'text-brand-muted' => ! $isActiveTag,
                            ])>
                                {{ $tag->posts_count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </aside>
        @endif
    </section>
@endsection
