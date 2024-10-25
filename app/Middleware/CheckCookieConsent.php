<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCookieConsent
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // If the user rejected cookies, clear session and CSRF cookies
        if ($request->cookies->get('cookie_consent') === 'rejected') {
            $response->headers->setCookie(cookie()->forget('laravel_session'));
            $response->headers->setCookie(cookie()->forget('XSRF-TOKEN'));
        }

        return $response;
    }
}
