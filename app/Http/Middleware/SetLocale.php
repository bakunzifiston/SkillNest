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
 * session, then the long-lived cookie, then the initial language for first-time visitors.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('localization.locales'));
        $default = config('localization.default');
        $initial = in_array(config('localization.initial'), $supported, true) ? config('localization.initial') : $default;
        $cookieName = config('localization.cookie');
        $sessionKey = config('localization.session_key');
        $user = $request->user();

        $userLocale = $user && in_array($user->locale, $supported, true) ? $user->locale : null;
        $guestChoice = $this->firstSupported($supported, [
            $request->hasSession() ? $request->session()->get($sessionKey) : null,
            $request->cookie($cookieName),
        ]);

        // A real choice (account, session, cookie) always wins; otherwise a first-time visitor gets the initial language.
        $choice = $userLocale ?? $guestChoice;
        $locale = $choice ?? $initial;

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

        // Remember a real choice in the session (the initial language is never stored).
        if ($choice !== null && $request->hasSession() && $request->session()->get($sessionKey) !== $choice) {
            $request->session()->put($sessionKey, $choice);
        }

        $response = $next($request);

        // The request itself may have changed the language (the switcher does): use the final choice,
        // not the one worked out before the request ran, or we would overwrite the new cookie.
        $finalChoice = $request->hasSession() && in_array($request->session()->get($sessionKey), $supported, true)
            ? $request->session()->get($sessionKey)
            : $choice;

        // Keep the cookie in step with a real choice, so it survives logout and new sessions.
        if ($finalChoice !== null && $request->cookie($cookieName) !== $finalChoice) {
            Cookie::queue($cookieName, $finalChoice, (int) config('localization.cookie_minutes'));
        }
        $response->headers->set('Content-Language', $finalChoice ?? $locale);

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
