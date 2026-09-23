<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RwandaLocations;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ...RwandaLocations::validationRules(),
        ]);

        $hierarchyErrors = RwandaLocations::validateHierarchy($validated);
        if ($hierarchyErrors !== []) {
            throw ValidationException::withMessages($hierarchyErrors);
        }

        if (($validated['country'] ?? null) !== RwandaLocations::DEFAULT_COUNTRY) {
            $validated['province'] = null;
            $validated['district'] = null;
            $validated['sector'] = null;
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'country' => $validated['country'],
            'province' => $validated['province'] ?? null,
            'district' => $validated['district'] ?? null,
            'sector' => $validated['sector'] ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
