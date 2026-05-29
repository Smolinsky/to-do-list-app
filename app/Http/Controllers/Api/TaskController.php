<?php

namespace App\Http\Controllers\Api;

use App\DTO\Task\CreateTaskData;
use App\DTO\Task\MoveTaskData;
use App\DTO\Task\UpdateTaskData;
use App\Filters\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\MoveTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {
    }

    public function getTasks(Filter $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $tasks = $user->tasks()
            ->withCount('attachments')
            ->filter($request)
            ->paginate(10);

        return TaskResource::collection($tasks);
    }

    public function createTask(CreateTaskRequest $request): JsonResponse
    {
        /** @var CreateTaskData $taskData */
        $taskData = $request->getDTO();

        $task = $this->taskService->createTask($taskData);

        return TaskResource::make(
            $task->loadCount('attachments')
        )->response()->setStatusCode(201);
    }

    public function getTask(Task $task): TaskResource
    {
        Gate::authorize('check-task', $task);

        return TaskResource::make(
            $task->load('attachments')->loadCount('attachments')
        );
    }

    public function updateTask(UpdateTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('check-task', $task);

        /** @var UpdateTaskData $taskData */
        $taskData = $request->getDTO();

        $task = $this->taskService->updateTask($task, $taskData);

        return new TaskResource(
            $task->load('attachments')->loadCount('attachments')
        );
    }

    public function moveTask(MoveTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('check-task', $task);

        /** @var MoveTaskData $taskData */
        $taskData = $request->getDTO();

        $task = $this->taskService->moveTask($task, $taskData);

        return new TaskResource(
            $task->load('attachments')->loadCount('attachments')
        );
    }

    public function deleteTask(Task $task): JsonResponse
    {
        Gate::authorize('check-task', $task);

        $this->taskService->archiveTask($task);

        return response()->json([
            'message' => 'Successfully archived',
        ]);
    }
}
