<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function testBadRequestErrorWhenEmailNotExists(): void
    {
        $this->postJson(route('api.forgot-password'), [
            'email' => 'test@test.com',
        ])->assertStatus(Response::HTTP_BAD_REQUEST);

        Notification::assertNothingSent();
    }

    public function testSendingResetPasswordLink(): void
    {
        Notification::fake();

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'test@test.com',
        ]);

        $response = $this->postJson(route('api.forgot-password'), [
            'email' => 'test@test.com',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
            ]);

        Notification::sent($user, ResetPassword::class);
    }

    public function testBadPasswordResetToken(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
        ]);

        Password::sendResetLink([
            'email' => 'test@test.com'
        ]);

        $this->assertDatabaseCount('password_reset_tokens', 1);

        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'test@test.com',
            'token' => 'bad-token',
            'password' => '11111111111',
            'password_confirmation' => '11111111111',
        ]);

        $response
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->assertTrue(
            Hash::check('password', $user->refresh()->password)
        );
    }

    public function testSuccessfulPasswordReset(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'test@test.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('api.reset-password'), [
            'email' => 'test@test.com',
            'token' => $token,
            'password' => '11111111111',
            'password_confirmation' => '11111111111',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertTrue(
            Hash::check('11111111111', $user->refresh()->password)
        );
    }
}
