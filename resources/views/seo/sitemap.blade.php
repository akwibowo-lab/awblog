<?php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('blog.index') }}</loc>
        @if ($latestPostDate)
            <lastmod>{{ $latestPostDate->toAtomString() }}</lastmod>
        @endif
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach ($posts as $post)
        <url>
            <loc>{{ $post->publicUrl() }}</loc>
            <lastmod>{{ ($post->updated_at ?? $post->published_at)->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach ($categories as $category)
        <url>
            <loc>{{ route('blog.category', $category) }}</loc>
            @if ($category->updated_at)
                <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            @endif
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
    @foreach ($tags as $tag)
        <url>
            <loc>{{ route('blog.tag', $tag) }}</loc>
            @if ($tag->updated_at)
                <lastmod>{{ $tag->updated_at->toAtomString() }}</lastmod>
            @endif
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach
</urlset>
