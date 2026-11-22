<?php

namespace App\Http\Controllers;

use App\Actions\Home\GetHomepageData;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(GetHomepageData $getHomepageData): View
    {
        app()->setLocale('id');
        Carbon::setLocale('id');

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
        ]);
    }
}
