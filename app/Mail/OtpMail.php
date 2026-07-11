<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Login OTP. Sent synchronously (not queued) — the user is on the OTP-entry
 * screen waiting for it, so a queue delay would just be dead time for them.
 */
class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $code,
    ) {}

    public function build(): self
    {
        return $this->subject('Your Shelvi Finance login code: '.$this->code)
            ->view('emails.otp');
    }
}
