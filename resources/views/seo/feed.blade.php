<?php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{{ __('blog.site_name') }}</title>
        <link>{{ route('blog.index') }}</link>
        <description>{{ __('blog.seo.index_description') }}</description>
        <language>id</language>
        <lastBuildDate>{{ $lastBuildDate->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('seo.feed') }}" rel="self" type="application/rss+xml" />
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ $post->publicUrl() }}</link>
                <guid isPermaLink="true">{{ $post->publicUrl() }}</guid>
                <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
                @if ($post->author)
                    <dc:creator>{{ $post->author->name }}</dc:creator>
                @endif
                @foreach ($post->categories as $category)
                    <category>{{ $category->name }}</category>
                @endforeach
                <description><![CDATA[{!! str_replace(']]>', ']]]]><![CDATA[>', $post->displayExcerpt(300)) !!}]]></description>
            </item>
        @endforeach
    </channel>
</rss>
