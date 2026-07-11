<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_a_known_email_sends_an_otp_instead_of_logging_in(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->post('/login', ['email' => $user->email]);

        $this->assertGuest();
        $response->assertRedirect(route('otp.verify'));
        Mail::assertSent(OtpMail::class);
    }

    public function test_users_can_authenticate_by_completing_the_otp_step(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email]);
        $code = $user->fresh()->otp_code;

        $response = $this->post('/otp/verify', ['code' => $code]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_complete_login_with_an_incorrect_otp(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email]);
        $this->post('/otp/verify', ['code' => '0000']);

        $this->assertGuest();
    }

    public function test_an_unknown_email_can_not_request_an_otp(): void
    {
        Mail::fake();

        $this->post('/login', ['email' => 'nobody@shelvi.test']);

        $this->assertGuest();
        Mail::assertNothingSent();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
