<?php

namespace App\Http\Middleware;

use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Choose the language for each request: ?lang= first, then the saved cookie, then Bengali.
 * Runs globally so error pages and AJAX responses use the visitor's language too.
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query(Localization::QUERY);
        $saved = $request->cookies->get(Localization::COOKIE);

        $locale = match (true) {
            Localization::isSupported($requested) => $requested,
            Localization::isSupported($saved) => $saved,
            default => Localization::DEFAULT,
        };

        App::setLocale($locale);

        $response = $next($request);

        if ($locale !== $saved && Localization::isSupported($requested)) {
            $response->headers->setCookie(Cookie::create(
                Localization::COOKIE,
                $locale,
                now()->addYears(5),
                path: config('session.path', '/'),
                domain: config('session.domain'),
                secure: config('session.secure'),
                sameSite: Cookie::SAMESITE_LAX,
            ));
        }

        return $response;
    }
}
