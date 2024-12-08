<?php

namespace App\Http\Controllers\Api;

use App\Filters\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
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
        $tasks = Task::filter($request)->paginate(10);

        return TaskResource::collection($tasks);
    }

    public function createTask(CreateTaskRequest $request): TaskResource
    {
        $task = $this->taskService->createTask(Auth::user(), $request->validated());

        return new TaskResource($task);
    }

    public function getTask(Task $task): TaskResource
    {
        Gate::authorize('check-task', $task);

        return TaskResource::make($task);
    }

    public function updateTask(UpdateTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('check-task', $task);

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function deleteTask(Task $task): JsonResponse
    {
        Gate::authorize('check-task', $task);

        $task->delete();

        return response()->json([
            'message' => 'Successfully deleted',
        ]);
    }
}
