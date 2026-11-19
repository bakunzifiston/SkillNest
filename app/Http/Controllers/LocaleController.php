<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /** Switch language: remembered in the session, a long-lived cookie, and (when signed in) on the account. */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys(config('localization.locales')))],
        ]);
        $locale = $validated['locale'];

        $request->session()->put(config('localization.session_key'), $locale);
        Cookie::queue(config('localization.cookie'), $locale, (int) config('localization.cookie_minutes'));

        if ($user = $request->user()) {
            $user->forceFill(['locale' => $locale])->save();
        }

        // Back to the page the learner was on, but never to another site.
        $previous = url()->previous();
        $target = Str::startsWith($previous, url('/')) ? $previous : route('home');

        return redirect()->to($target);
    }
}
