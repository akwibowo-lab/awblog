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

        return view('home', [
            'projects' => $data['projects'],
            'posts' => $data['posts'],
            'portfolioPdfUrl' => $data['portfolioPdfUrl'],
            'seoTitle' => __('home.seo.title'),
            'seoDescription' => __('home.seo.description'),
            'canonicalUrl' => route('home'),
            'ogType' => 'website',
            'ogImage' => asset('branding/aw-orbit-dark.svg'),
        ]);
    }
}
