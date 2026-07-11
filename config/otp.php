<?php

// Login OTP settings. `recipient` is a temporary override so every login OTP
// goes to one inbox during setup/testing — remove it (or point OTP_RECIPIENT_EMAIL
// at nothing) once real per-user delivery is wanted, and the code will fall back
// to sending each OTP to the logging-in user's own email address.
return [
    'recipient' => env('OTP_RECIPIENT_EMAIL', 'jeetendraparmar22@gmail.com'),
    'ttl_minutes' => 10,
    'resend_seconds' => 30,
];
