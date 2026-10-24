<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => [
                'required',
                'bail',
                'string',
                'max:254',
                function ($attribute, $value, $fail): void {
                    if (preg_match('/[\r\n\x00]/', $value) === 1) {
                        $fail('Alamat email tidak valid.');
                    }
                },
                'email:rfc',
            ],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        Password::sendResetLink($request->only('email'));

        // Use the same response for known and unknown accounts so the reset
        // form cannot be used to enumerate the single administrator address.
        return back()->with('status', 'Jika alamat email terdaftar, instruksi reset kata sandi akan dikirim.');
    }
}
