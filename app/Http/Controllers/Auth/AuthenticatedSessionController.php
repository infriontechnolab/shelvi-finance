<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Otp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request: verify credentials, then send
     * a login OTP rather than signing the user in immediately — the session
     * only becomes authenticated once OtpVerificationController confirms it.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        Otp::issue($user);

        $request->session()->put('otp.user_id', $user->id);
        $request->session()->put('otp.remember', $request->boolean('remember'));

        return redirect()->route('otp.verify');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
