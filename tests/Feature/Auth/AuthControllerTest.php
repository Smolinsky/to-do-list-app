<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUserReceivedAccessTokenOnSuccessfulLogin(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'access_token',
            ]);

        $this->assertEquals(1, $user->tokens()->count());
    }

    public function testUserReceivedErrorIfCredentialsNotCorrect(): void
    {
        $response = $this->postJson(route('api.login'), [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function testUnauthorizedUserLogout(): void
    {
        $this
            ->postJson(route('api.logout'))
            ->assertUnauthorized()
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function testSuccessfulLogout(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $accessToken = $user->createToken('api');

        $this->assertEquals(1, $user->tokens()->count());

        $this
            ->withToken($accessToken->plainTextToken)
            ->postJson(route('api.logout'))
            ->assertOk()
            ->assertJsonStructure([
                'message'
            ]);

        $this->assertEquals(0, $user->tokens()->count());
    }
}
