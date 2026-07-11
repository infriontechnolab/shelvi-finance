<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Otp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Second step of login: the user has already passed the password check
 * (AuthenticatedSessionController::store) and now has a pending OTP recorded
 * against their account. This controller confirms it and signs them in.
 */
class OtpVerificationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', ['email' => $user->email]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate(['code' => ['required', 'digits:4']]);

        $throttleKey = 'otp-verify:'.$user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Please wait '.RateLimiter::availableIn($throttleKey).' seconds and try again.',
            ]);
        }

        if (! Otp::verify($user, $request->string('code'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'code' => 'That code is incorrect or has expired.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $remember = (bool) $request->session()->pull('otp.remember', false);
        $request->session()->forget('otp.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $throttleKey = 'otp-resend:'.$user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            return back()->with('status', 'Please wait '.RateLimiter::availableIn($throttleKey).' seconds before requesting another code.');
        }

        RateLimiter::hit($throttleKey, config('otp.resend_seconds'));
        Otp::issue($user);

        return back()->with('status', 'A new code has been sent.');
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('otp.user_id');

        return $id ? User::find($id) : null;
    }
}
