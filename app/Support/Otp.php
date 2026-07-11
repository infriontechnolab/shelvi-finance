<?php

namespace App\Support;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/** Issues and verifies the 4-digit login OTP stored on the user record. */
class Otp
{
    /** Generate a fresh code, save it, and email it. */
    public static function issue(User $user): void
    {
        $code = (string) random_int(1000, 9999);

        $user->forceFill([
            'otp_code' => $code,
            'otp_expires_at' => now()->addMinutes(config('otp.ttl_minutes')),
        ])->save();

        // config('otp.recipient') is a temporary override so every OTP lands in one
        // inbox during setup — falls back to the user's own email once that's unset.
        Mail::to(config('otp.recipient') ?: $user->email)->send(new OtpMail($user, $code));
    }

    /** Check the code, and clear it (single use) if it matches and hasn't expired. */
    public static function verify(User $user, string $code): bool
    {
        $valid = $user->otp_code !== null
            && hash_equals($user->otp_code, $code)
            && $user->otp_expires_at?->isFuture();

        if ($valid) {
            self::clear($user);
        }

        return $valid;
    }

    public static function clear(User $user): void
    {
        $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();
    }
}
