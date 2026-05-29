<?php

namespace App\Http\Resources;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'tasks_count' => $this->whenCounted('tasks'),
            'columns' => $this->whenLoaded('tasks', function () {
                return collect(TaskStatus::cases())
                    ->map(function (TaskStatus $status) {
                        $tasks = $this->tasks
                            ->filter(function (Task $task) use ($status) {
                                return $task->status?->value === $status->value;
                            })
                            ->sortBy('position')
                            ->values();

                        return [
                            'status' => $status->value,
                            'tasks' => TaskResource::collection($tasks),
                        ];
                    })
                    ->values();
            }),
        ];
    }
}
