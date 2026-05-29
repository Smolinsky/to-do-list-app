<?php

namespace Tests\Unit\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUserGetTasksWithFilterByStatus()
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::COMPLETED,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::PENDING,
        ]);

        $this->actingAs($user)
            ->getJson('/api/tasks?status=completed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'status' => TaskStatus::COMPLETED->value,
            ]);
    }

    public function testUserGetTasksWithFilterByPriority()
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => TaskPriority::MEDIUM,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => TaskPriority::LOW,
        ]);

        $this->actingAs($user)
            ->getJson('/api/tasks?priority=medium')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'priority' => TaskPriority::MEDIUM->value,
            ]);
    }

    public function testUserGetTasksWithFilterByStatusAndPriority()
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::COMPLETED,
            'priority' => TaskPriority::MEDIUM,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::COMPLETED,
            'priority' => TaskPriority::LOW,
        ]);

        $this->actingAs($user)
            ->getJson('/api/tasks?status=completed&priority=medium')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'status' => TaskStatus::COMPLETED->value,
                'priority' => TaskPriority::MEDIUM->value,
            ]);
    }

    public function testUserGetTasksWithFilterByWrongValue()
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => TaskPriority::MEDIUM,
        ]);

        $this->actingAs($user)
            ->getJson('/api/tasks?status=completed1&priority=medium')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
