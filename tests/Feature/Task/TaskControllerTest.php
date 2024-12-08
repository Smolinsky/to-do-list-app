<?php

namespace Tests\Feature\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('check-task', function ($user, $task) {
            return $user->id === $task->user_id;
        });
    }

    public function testUserCanCreateTask()
    {
        $user = User::factory()->create();

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => TaskStatus::PENDING->value,
            'priority' => TaskPriority::MEDIUM->value,
            'due_date' => now()->addDays(7)->toDateString()
        ];

        $this->actingAs($user)
            ->postJson(route('api.tasks.create'), $taskData)
            ->assertCreated()
            ->assertJsonFragment([
                'title' => 'New Task',
                'description' => 'Task description',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => $taskData['due_date'],
            ]);
    }

    public function testUserCanGetTasks()
    {
        $user = User::factory()->create();
        Task::factory()->count(5)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user)
            ->getJson(route('api.tasks'))
            ->assertOk()
            ->assertJsonStructure(structure: [
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'due_date'
                    ]
                ]
            ]);
    }

    public function testUserCanShowGetTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addDays(5)
        ]);

        $this->actingAs($user)
            ->getJson(route('api.tasks.show', $task))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date->toDateString(),
            ]);
    }

    public function testUserCanUpdateTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id
        ]);

        $updatedData = [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'status' => 'completed',
            'priority' => 'high',
            'due_date' => now()->addDays(10)->toDateString(),
        ];

        $this->actingAs($user)
            ->putJson(route('api.tasks.update', $task), $updatedData)
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Updated Task Title',
                'description' => 'Updated description',
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => $updatedData['due_date'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'status' => 'completed',
            'priority' => 'high',
            'due_date' => $updatedData['due_date'],
        ]);
    }

    public function testUserCanDeleteTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user)
            ->deleteJson(route('api.tasks.delete', $task))
            ->assertOk()
            ->assertJson(['message' => 'Successfully deleted']);
    }

    public function testUnauthorizedUserCannotDeleteTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id
        ]);

        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->deleteJson(route('api.tasks.delete', $task))
            ->assertForbidden();
    }
}
