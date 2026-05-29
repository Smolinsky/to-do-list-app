<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthenticatedUserCanFetchCurrentUser(): void
    {
        $user = User::factory()->create([
            'email' => 'current@example.com',
        ]);

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', 'current@example.com');
    }

    public function testGuestCannotFetchCurrentUser(): void
    {
        $this->getJson('/api/user')
            ->assertUnauthorized();
    }
}
