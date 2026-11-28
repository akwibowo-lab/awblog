<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetPublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $targetLocale = in_array($locale, SetPublicLocale::SUPPORTED_LOCALES, true)
            ? $locale
            : 'id';

        return redirect()
            ->back(fallback: route('home'))
            ->withCookie(cookie(
                SetPublicLocale::COOKIE_NAME,
                $targetLocale,
                60 * 24 * 365,
                '/',
                null,
                $request->isSecure(),
                false,
                false,
                'lax'
            ));
    }
}
