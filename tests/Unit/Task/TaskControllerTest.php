<?php

namespace Tests\Unit\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    public function testUserGetTasksWithFilterByStatus()
    {
        $user = User::factory()->create();
        Auth::login($user);

        Task::factory()->count(5)->create();

        $response = $this->json('GET', '/api/tasks', [
            'status' => 'completed',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'status',
                        'priority',
                        'due_date',
                    ],
                ],
            ]);
    }

    public function testUserGetTasksWithFilterByPriority()
    {
        $user = User::factory()->create();
        Auth::login($user);

        Task::factory()->count(5)->create();

        $response = $this->json('GET', '/api/tasks', [
            'priority' => 'medium',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'status',
                        'priority',
                        'due_date',
                    ],
                ],
            ]);
    }

    public function testUserGetTasksWithFilterByStatusAndPriority()
    {
        $user = User::factory()->create();
        Auth::login($user);

        Task::factory()->count(5)->create();

        $response = $this->json('GET', '/api/tasks', [
            'status' => 'completed',
            'priority' => 'medium',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'status',
                        'priority',
                        'due_date',
                    ],
                ],
            ]);
    }

    public function testUserGetTasksWithFilterByWrongValue()
    {
        $user = User::factory()->create();
        Auth::login($user);

        Task::factory()->count(5)->create();

        $response = $this->json('GET', '/api/tasks', [
            'status' => 'completed1',
            'priority' => 'medium',
        ]);

        $response->assertOk();
    }
}
