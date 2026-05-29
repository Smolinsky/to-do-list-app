<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function testUserCanRegisterSuccessfully(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->postJson(route('api.register'), $payload)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('name', 'John Doe')
            ->assertJsonPath('email', 'john@example.com')
            ->assertJsonStructure([
                'accessToken',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function testUserCannotRegisterWithDuplicateEmail(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $this->postJson(route('api.register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('email');
    }
}
