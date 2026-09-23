<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view, or a friendly invalid/expired state.
     */
    public function create(Request $request): View
    {
        $token = (string) $request->route('token');
        $email = strtolower(trim((string) $request->query('email', $request->input('email', ''))));

        if ($token === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return view('auth.reset-password-invalid');
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Password::tokenExists($user, $token)) {
            return view('auth.reset-password-invalid');
        }

        return view('auth.reset-password', [
            'request' => $request,
            'email' => $email,
            'token' => $token,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $email]);

        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.confirmed' => 'The passwords do not match.',
            'password.min' => 'The password must be at least :min characters.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                // Let the User "hashed" cast hash the password once.
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Password reset successfully. You can now sign in using your new password.');
        }

        if ($status === Password::INVALID_TOKEN) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'This password reset link is invalid or has expired. Please request a new password reset link.',
                ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
