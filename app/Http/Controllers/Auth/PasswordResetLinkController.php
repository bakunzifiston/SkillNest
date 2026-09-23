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
            $status = Password::sendResetLink($request->only('email'));

            // Log broker outcome for ops (not shown to the browser).
            Log::info('Password reset link requested.', [
                'status' => $status,
                'mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
        } catch (Throwable $e) {
            Log::error('Password reset email failed to send.', [
                'exception' => $e->getMessage(),
                'mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);
        }

        return back()->with(
            'status',
            'If an account exists with that email address, we\'ve sent a password reset link.'
        );
    }
}
