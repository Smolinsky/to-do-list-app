<?php

namespace Tests\Feature\Board;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanGetBoardsList(): void
    {
        $user = User::factory()->create();
        Board::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);
        Board::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.boards'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'tasks_count',
                    ],
                ],
            ]);
    }

    public function testUserCanCreateBoard(): void
    {
        $user = User::factory()->create();

        $boardData = [
            'name' => 'Backend Board',
            'description' => 'Tasks for API improvements',
        ];

        $this->actingAs($user)
            ->postJson(route('api.boards.create'), $boardData)
            ->assertCreated()
            ->assertJsonFragment($boardData);
    }

    public function testUserCanUpdateBoard(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old name',
            'description' => 'Old description',
        ]);

        $this->actingAs($user)
            ->putJson(route('api.boards.update', $board), [
                'name' => 'New name',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New name');

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'New name',
            'description' => 'Old description',
        ]);
    }

    public function testUserCanGetBoardWithKanbanColumns(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
        ]);

        $pendingTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::PENDING,
            'priority' => TaskPriority::LOW,
            'position' => 0,
        ]);

        $inProgressTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::IN_PROGRESS,
            'priority' => TaskPriority::MEDIUM,
            'position' => 1,
        ]);

        $completedTask = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'status' => TaskStatus::COMPLETED,
            'priority' => TaskPriority::HIGH,
            'position' => 2,
        ]);

        $this->actingAs($user)
            ->getJson(route('api.boards.show', $board))
            ->assertOk()
            ->assertJsonPath('data.columns.0.status', TaskStatus::PENDING->value)
            ->assertJsonPath('data.columns.0.tasks.0.id', $pendingTask->id)
            ->assertJsonPath('data.columns.1.status', TaskStatus::IN_PROGRESS->value)
            ->assertJsonPath('data.columns.1.tasks.0.id', $inProgressTask->id)
            ->assertJsonPath('data.columns.2.status', TaskStatus::COMPLETED->value)
            ->assertJsonPath('data.columns.2.tasks.0.id', $completedTask->id);
    }

    public function testUserCannotAccessAnotherUsersBoard(): void
    {
        $boardOwner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $boardOwner->id,
        ]);

        $this->actingAs($anotherUser)
            ->getJson(route('api.boards.show', $board))
            ->assertForbidden();
    }

    public function testUserCannotUpdateAnotherUsersBoard(): void
    {
        $boardOwner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $boardOwner->id,
            'name' => 'Board name',
        ]);

        $this->actingAs($anotherUser)
            ->putJson(route('api.boards.update', $board), [
                'name' => 'Updated by another user',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Board name',
        ]);
    }

    public function testDeletingBoardDetachesTasks(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('api.boards.delete', $board))
            ->assertOk();

        $this->assertDatabaseMissing('boards', [
            'id' => $board->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'board_id' => null,
        ]);
    }
}
