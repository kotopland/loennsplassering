<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCookieConsent
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $consent = $request->cookies->get('cookie_consent');

        $response = $next($request);

        if ($consent === 'rejected') {
            // Clear session and CSRF cookies if consent is rejected.
            $response->headers->setCookie(cookie()->forget('frikirkens_lonnsberegner_session')->withPath('/'));
            $response->headers->setCookie(cookie()->forget('laravel_session')->withPath('/'));
            $response->headers->setCookie(cookie()->forget('XSRF-TOKEN')->withPath('/'));
        }

        return $response;
    }
}
