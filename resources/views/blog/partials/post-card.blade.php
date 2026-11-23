@php
    $media = $post->featuredMedia;
    $dimensions = $media?->variantDimensions('medium') ?? ['width' => null, 'height' => null];
    $primaryCategory = $post->categories->first();
@endphp

<article class="flex h-full flex-col overflow-hidden rounded-xl border border-brand-border bg-brand-surface transition-colors hover:border-brand-accent/60">
    @if ($media)
        <a href="{{ $post->publicUrl() }}" class="block aspect-video w-full overflow-hidden bg-brand-bg">
            <img
                src="{{ $media->variantUrl('medium') }}"
                alt="{{ $media->altText($post->title) }}"
                @if ($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                @if ($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover"
            >
        </a>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-brand-muted">
            @if ($primaryCategory)
                <a
                    href="{{ route('blog.category', ['category' => $primaryCategory->slug]) }}"
                    class="font-mono font-medium text-brand-accent hover:underline"
                >
                    {{ $primaryCategory->name }}
                </a>
                <span aria-hidden="true">/</span>
            @endif

            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->translatedFormat('d F Y') }}
                </time>
            @endif

            @if ($post->reading_time_minutes)
                <span aria-hidden="true">/</span>
                <span>{{ __('blog.show.reading_time', ['minutes' => $post->reading_time_minutes]) }}</span>
            @endif
        </div>

        <h2 class="mt-2.5 font-display text-lg font-semibold leading-snug text-brand-text">
            <a href="{{ $post->publicUrl() }}" class="hover:text-brand-accent transition-colors">
                {{ $post->title }}
            </a>
        </h2>

        <p class="mt-2.5 flex-1 text-sm leading-relaxed text-brand-muted">
            {{ $post->displayExcerpt(140) }}
        </p>

        <div class="mt-4 pt-3 border-t border-brand-border/70">
            <a
                href="{{ $post->publicUrl() }}"
                class="inline-flex min-h-11 items-center text-sm font-medium text-brand-accent-2 hover:text-brand-accent transition-colors"
            >
                {{ __('blog.index.read_article') }}
            </a>
        </div>
    </div>
</article>
