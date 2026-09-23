<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * Always returns a generic success message to prevent email enumeration.
     */
    public function store(Request $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $email]);

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            // Attempt to send when the account exists. Throttling is enforced by the broker.
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            // Do not expose mail/transport failures to the browser (and avoid
            // turning existing-account mail errors into an enumeration signal).
            Log::error('Password reset email failed to send.', [
                'exception' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'status',
            'If an account exists with that email address, we\'ve sent a password reset link.'
        );
    }
}
