<?php

namespace App\Services\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ContentCache
{
    private const VERSION_PREFIX = 'content_cache.version.';

    /**
     * Remember a value scoped by a domain version so file/database cache stores can invalidate cleanly.
     *
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public static function remember(string $domain, string $key, int $ttlSeconds, Closure $callback): mixed
    {
        try {
            $version = self::version($domain);
            $scopedKey = "content_cache.{$domain}.v{$version}.{$key}";

            return Cache::remember($scopedKey, now()->addSeconds($ttlSeconds), $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    /**
     * Bump a domain's cache version to invalidate all entries in that domain.
     */
    public static function bump(string $domain): void
    {
        try {
            $next = self::version($domain) + 1;
            Cache::forever(self::VERSION_PREFIX.$domain, $next);
        } catch (Throwable) {
            // Ignore cache store failures during model events.
        }
    }

    public static function version(string $domain): int
    {
        return (int) Cache::rememberForever(self::VERSION_PREFIX.$domain, static fn (): int => 1);
    }
}
