<?php

namespace App\Services;

use App\DTO\Task\CreateTaskData;
use App\DTO\Task\MoveTaskData;
use App\DTO\Task\UpdateTaskData;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function createTask(CreateTaskData $taskData): Task
    {
        return DB::transaction(function () use ($taskData) {
            $taskAttributes = $taskData->toTaskAttributes();

            if ($taskData->position === null) {
                $taskAttributes['position'] = $this->resolvePosition($taskData->user, $taskAttributes);

                return $taskData->user->tasks()->create($taskAttributes);
            }

            $task = $taskData->user->tasks()->create([
                ...$taskAttributes,
                'position' => 0,
            ]);

            return $this->moveTaskInternal($task, $this->extractMovementData($taskAttributes));
        });
    }

    public function updateTask(Task $task, UpdateTaskData $taskData): Task
    {
        return DB::transaction(function () use ($task, $taskData) {
            $movementData = $taskData->toMovementAttributes();
            $attributes = $taskData->toContentAttributes();

            if ($attributes !== []) {
                $task->update($attributes);
                $task->refresh();
            }

            if ($movementData !== []) {
                return $this->moveTaskInternal($task, $movementData);
            }

            return $task->refresh();
        });
    }

    public function moveTask(Task $task, MoveTaskData $moveData): Task
    {
        $attributes = $moveData->toAttributes();

        if ($attributes === []) {
            return $task->refresh();
        }

        return DB::transaction(fn() => $this->moveTaskInternal($task, $attributes));
    }

    public function archiveTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $task->refresh();

            $this->syncColumnPositions(
                $task->user,
                $task->board_id,
                $task->status->value,
                excludeTaskId: $task->id,
            );

            $task->delete();
        });
    }

    private function moveTaskInternal(Task $task, array $moveData): Task
    {
        $task->refresh();

        $sourceBoardId = $task->board_id;
        $sourceStatus = $task->status->value;
        $targetBoardId = array_key_exists('board_id', $moveData) ? $moveData['board_id'] : $sourceBoardId;
        $targetStatus = $moveData['status'] ?? $sourceStatus;
        $targetPosition = $moveData['position'] ?? null;

        if ($sourceBoardId !== $targetBoardId || $sourceStatus !== $targetStatus) {
            $this->syncColumnPositions(
                $task->user,
                $sourceBoardId,
                $sourceStatus,
                excludeTaskId: $task->id,
            );
        }

        $this->syncColumnPositions(
            $task->user,
            $targetBoardId,
            $targetStatus,
            taskToInsert: $task,
            targetPosition: $targetPosition,
        );

        return $task->refresh();
    }

    private function resolvePosition(User $user, array $taskData, ?Task $task = null): int
    {
        if (array_key_exists('position', $taskData) && $taskData['position'] !== null) {
            return (int) $taskData['position'];
        }

        $boardId = $taskData['board_id'] ?? $task?->board_id;
        $status = $taskData['status'] ?? $task?->status?->value;

        return (int) ($this->buildPositionQuery($user, $boardId, $status, $task)->max('position') ?? -1) + 1;
    }

    private function syncColumnPositions(
        User $user,
        ?int $boardId,
        string $status,
        ?Task $taskToInsert = null,
        ?int $targetPosition = null,
        ?int $excludeTaskId = null,
    ): void {
        $excludedTaskId = $taskToInsert?->id ?? $excludeTaskId;

        $taskIds = $this->buildColumnQuery($user, $boardId, $status, $excludedTaskId)
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($taskToInsert !== null) {
            $insertPosition = $targetPosition ?? count($taskIds);
            $insertPosition = min(max($insertPosition, 0), count($taskIds));

            array_splice($taskIds, $insertPosition, 0, [$taskToInsert->id]);
        }

        foreach ($taskIds as $index => $taskId) {
            $data = ['position' => $index];

            if ($taskToInsert !== null && $taskId === $taskToInsert->id) {
                $data['board_id'] = $boardId;
                $data['status'] = $status;
            }

            Task::query()->whereKey($taskId)->update($data);
        }
    }

    private function extractMovementData(array $taskData): array
    {
        return array_intersect_key($taskData, array_flip([
            'board_id',
            'status',
            'position',
        ]));
    }

    private function buildPositionQuery(User $user, ?int $boardId, ?string $status, ?Task $task = null): HasMany
    {
        return $this->buildColumnQuery($user, $boardId, $status, $task?->id);
    }

    private function buildColumnQuery(User $user, ?int $boardId, ?string $status, ?int $excludeTaskId = null): HasMany
    {
        $query = $user->tasks();

        if ($boardId === null) {
            $query->whereNull('board_id');
        } else {
            $query->where('board_id', $boardId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($excludeTaskId !== null) {
            $query->whereKeyNot($excludeTaskId);
        }

        return $query;
    }
}
