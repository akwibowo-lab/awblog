<?php

namespace App\Http\Middleware;

use App\Actions\Seo\ResolveLegacyRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLegacyRedirects
{
    public function __construct(
        private readonly ResolveLegacyRedirect $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $match = $this->resolver->execute($request);

        if ($match !== null) {
            return redirect()->to($match['target'], $match['status']);
        }

        return $next($request);
    }
}
