<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public const BYPASS_COOKIE = 'awblog_maintenance_bypass';

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldAlwaysAllow($request)) {
            return $next($request);
        }

        $settings = SiteSetting::getMaintenance();
        $isAdminPath = $this->isAdminPath($request);
        $enabled = $isAdminPath
            ? (bool) ($settings['backend_enabled'] ?? false)
            : (bool) ($settings['frontend_enabled'] ?? false);

        if (! $enabled) {
            return $next($request);
        }

        if ($isAdminPath && auth()->check()) {
            return $next($request);
        }

        $bypassToken = (string) ($settings['bypass_token'] ?? '');

        if ($bypassToken !== '') {
            $queryToken = (string) $request->query('bypass', '');

            if ($queryToken !== '' && hash_equals($bypassToken, $queryToken)) {
                return $next($request)->withCookie(cookie(
                    name: self::BYPASS_COOKIE,
                    value: hash('sha256', $bypassToken),
                    minutes: 60 * 12,
                    path: '/',
                    httpOnly: true,
                    sameSite: 'lax',
                ));
            }

            $cookieToken = (string) $request->cookie(self::BYPASS_COOKIE, '');

            if ($cookieToken !== '' && hash_equals(hash('sha256', $bypassToken), $cookieToken)) {
                return $next($request);
            }
        }

        return response()->view('errors.maintenance', [
            'title' => (string) ($settings['title'] ?? 'Situs Sedang Dalam Pemeliharaan'),
            'message' => (string) ($settings['message'] ?? 'Kami sedang melakukan pembaruan dan peningkatan layanan. Silakan kembali beberapa saat lagi.'),
            'isBackend' => $isAdminPath,
        ], 503);
    }

    private function shouldAlwaysAllow(Request $request): bool
    {
        return $request->is('up')
            || $request->is('branding/*')
            || $request->is('build/*')
            || $request->is('storage/*');
    }

    private function isAdminPath(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*')
            || $request->is('livewire/*')
            || $request->is('livewire-*/*');
    }
}
