<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Picks the language for each request: the signed-in user's saved preference first, then the
 * session, then the long-lived cookie, then the default (English).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('localization.locales'));
        $default = config('localization.default');
        $cookieName = config('localization.cookie');
        $user = $request->user();

        $userLocale = $user && in_array($user->locale, $supported, true) ? $user->locale : null;
        $guestChoice = $this->firstSupported($supported, [
            $request->hasSession() ? $request->session()->get('locale') : null,
            $request->cookie($cookieName),
        ]);

        $locale = $userLocale ?? $guestChoice ?? $default;

        // A signed-in user who has never chosen adopts what they picked before logging in.
        if ($user && $userLocale === null && $guestChoice !== null) {
            try {
                $user->forceFill(['locale' => $guestChoice])->saveQuietly();
            } catch (Throwable) {
                // Column not migrated yet on this server: carry on with the session/cookie choice.
            }
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if ($request->hasSession() && $request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);

        // The request itself may have changed the language (the switcher does): use the final choice,
        // not the one worked out before the request ran, or we would overwrite the new cookie.
        $final = $request->hasSession() && in_array($request->session()->get('locale'), $supported, true)
            ? $request->session()->get('locale')
            : $locale;

        // Keep the cookie in step so the choice survives logout and new sessions.
        if ($request->cookie($cookieName) !== $final) {
            Cookie::queue($cookieName, $final, (int) config('localization.cookie_minutes'));
        }
        $response->headers->set('Content-Language', $final);

        return $response;
    }

    /** @param  list<string>  $supported */
    private function firstSupported(array $supported, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
