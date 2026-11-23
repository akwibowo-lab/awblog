<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Services\Cache\ContentCache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $bumpBlogAndSeo = static function (): void {
            ContentCache::bump('blog');
            ContentCache::bump('seo');
        };

        $bumpHomepageBlogAndSeo = static function () use ($bumpBlogAndSeo): void {
            ContentCache::bump('homepage');
            $bumpBlogAndSeo();
        };

        $bumpHomepageAndProjects = static function (): void {
            ContentCache::bump('homepage');
            ContentCache::bump('projects');
        };

        Post::saved($bumpHomepageBlogAndSeo);
        Post::deleted($bumpHomepageBlogAndSeo);

        Category::saved($bumpBlogAndSeo);
        Category::deleted($bumpBlogAndSeo);

        Tag::saved($bumpBlogAndSeo);
        Tag::deleted($bumpBlogAndSeo);

        Project::saved($bumpHomepageAndProjects);
        Project::deleted($bumpHomepageAndProjects);

        Media::saved(static function (): void {
            ContentCache::bump('homepage');
            ContentCache::bump('projects');
            ContentCache::bump('blog');
        });
        Media::deleted(static function (): void {
            ContentCache::bump('homepage');
            ContentCache::bump('projects');
            ContentCache::bump('blog');
        });

        SiteSetting::saved(static fn () => ContentCache::bump('homepage'));
        SiteSetting::deleted(static fn () => ContentCache::bump('homepage'));
    }
}
