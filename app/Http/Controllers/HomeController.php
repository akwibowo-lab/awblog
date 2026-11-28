<?php

namespace App\Http\Controllers;

use App\Actions\Home\GetHomepageData;
use App\Services\Cache\ContentCache;
use Carbon\Carbon;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    private const CACHE_TTL_SECONDS = 1800;

    public function index(GetHomepageData $getHomepageData): Response
    {
        $locale = app()->getLocale();
        $hostKey = md5(request()->getSchemeAndHttpHost());

        $html = ContentCache::remember('homepage', "html.{$locale}.{$hostKey}", self::CACHE_TTL_SECONDS, static function () use ($getHomepageData, $locale): string {
            app()->setLocale($locale);
            Carbon::setLocale($locale);

            $data = $getHomepageData->execute();
            $settings = $data['settings'];

            return view('home', [
                'projects' => $data['projects'],
                'posts' => $data['posts'],
                'portfolioPdfUrl' => $data['portfolioPdfUrl'],
                'heroImageUrl' => $data['heroImageUrl'],
                'hasCustomHeroImage' => $data['hasCustomHeroImage'],
                'settings' => $settings,
                'seoTitle' => $settings['seo_title'] ?? __('home.seo.title'),
                'seoDescription' => $settings['seo_description'] ?? __('home.seo.description'),
                'canonicalUrl' => route('home'),
                'ogType' => 'website',
                'ogImage' => $data['heroImageUrl'],
            ])->render();
        });

        return response($html);
    }
}
