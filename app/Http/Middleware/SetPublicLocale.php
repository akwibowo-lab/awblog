<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    public const COOKIE_NAME = 'awblog_locale';

    /**
     * @var list<string>
     */
    public const SUPPORTED_LOCALES = ['id', 'en'];

    /**
     * Apply the user's selected locale on bilingual public routes and force Indonesian elsewhere.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'id';

        if ($request->routeIs('home', 'projects.index', 'projects.show')) {
            $cookieLocale = $request->cookie(self::COOKIE_NAME);

            if (is_string($cookieLocale) && in_array($cookieLocale, self::SUPPORTED_LOCALES, true)) {
                $locale = $cookieLocale;
            } else {
                $locale = $this->resolveRegionLocale($request);
            }
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    private function resolveRegionLocale(Request $request): string
    {
        $country = strtoupper(trim((string) $request->header('CF-IPCountry', '')));

        if ($country !== '' && $country !== 'ID' && $country !== 'XX' && $country !== 'T1') {
            return 'en';
        }

        return 'id';
    }
}
