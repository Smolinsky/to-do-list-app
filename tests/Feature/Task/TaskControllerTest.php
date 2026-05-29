<?php

namespace Tests\Feature\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Board;
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
                'status' => $task->status->value,
                'priority' => $task->priority->value,
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
        ]);

        $this->assertEquals(
            $updatedData['due_date'],
            $task->refresh()->due_date->toDateString()
        );
    }

    public function testUserCanArchiveTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user)
            ->deleteJson(route('api.tasks.delete', $task))
            ->assertOk()
            ->assertJson(['message' => 'Successfully archived']);

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    public function testArchivingTaskReordersRemainingTasksInColumn(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
        ]);

        $firstTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'position' => 0,
        ]);
        $taskToArchive = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'position' => 1,
        ]);
        $thirdTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('api.tasks.delete', $taskToArchive))
            ->assertOk();

        $this->assertSoftDeleted('tasks', [
            'id' => $taskToArchive->id,
        ]);
        $this->assertEquals(0, $firstTask->refresh()->position);
        $this->assertEquals(1, $thirdTask->refresh()->position);
    }

    public function testArchivedTaskIsHiddenFromTaskListAndShowRoute(): void
    {
        $user = User::factory()->create();
        $activeTask = Task::factory()->create([
            'user_id' => $user->id,
        ]);
        $archivedTask = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('api.tasks.delete', $archivedTask))
            ->assertOk();

        $this->actingAs($user)
            ->getJson(route('api.tasks'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $activeTask->id,
            ])
            ->assertJsonMissing([
                'id' => $archivedTask->id,
            ]);

        $this->actingAs($user)
            ->getJson(route('api.tasks.show', $archivedTask->id))
            ->assertNotFound();
    }

    public function testUserCanMoveTaskWithinSameColumn(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
        ]);

        $firstTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'position' => 0,
        ]);
        $secondTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'position' => 1,
        ]);
        $thirdTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->patchJson(route('api.tasks.move', $thirdTask), [
                'position' => 0,
            ])
            ->assertOk()
            ->assertJsonFragment([
                'id' => $thirdTask->id,
                'position' => 0,
            ]);

        $this->assertEquals(0, $thirdTask->refresh()->position);
        $this->assertEquals(1, $firstTask->refresh()->position);
        $this->assertEquals(2, $secondTask->refresh()->position);
    }

    public function testUserCanMoveTaskToAnotherBoardAndStatus(): void
    {
        $user = User::factory()->create();
        $sourceBoard = Board::factory()->create([
            'user_id' => $user->id,
        ]);
        $destinationBoard = Board::factory()->create([
            'user_id' => $user->id,
        ]);

        $taskToMove = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $sourceBoard->id,
            'status' => TaskStatus::PENDING,
            'position' => 0,
        ]);
        $remainingTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $sourceBoard->id,
            'status' => TaskStatus::PENDING,
            'position' => 1,
        ]);
        $destinationTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $destinationBoard->id,
            'status' => TaskStatus::IN_PROGRESS,
            'position' => 0,
        ]);

        $this->actingAs($user)
            ->patchJson(route('api.tasks.move', $taskToMove), [
                'board_id' => $destinationBoard->id,
                'status' => TaskStatus::IN_PROGRESS->value,
                'position' => 0,
            ])
            ->assertOk()
            ->assertJsonFragment([
                'id' => $taskToMove->id,
                'board_id' => $destinationBoard->id,
                'status' => TaskStatus::IN_PROGRESS->value,
                'position' => 0,
            ]);

        $this->assertEquals($destinationBoard->id, $taskToMove->refresh()->board_id);
        $this->assertEquals(TaskStatus::IN_PROGRESS, $taskToMove->refresh()->status);
        $this->assertEquals(0, $taskToMove->refresh()->position);
        $this->assertEquals(0, $remainingTask->refresh()->position);
        $this->assertEquals(1, $destinationTask->refresh()->position);
    }

    public function testUnauthorizedUserCannotMoveTask(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($anotherUser)
            ->patchJson(route('api.tasks.move', $task), [
                'position' => 0,
            ])
            ->assertForbidden();
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
