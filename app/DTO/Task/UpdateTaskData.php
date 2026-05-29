<?php

namespace App\DTO\Task;

use App\DTO\Concerns\TransformsDataValues;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateTaskData extends Data
{
    use TransformsDataValues;

    public function __construct(
        public readonly int|null|Optional $board_id,
        public readonly string|Optional $title,
        public readonly string|null|Optional $description,
        public readonly TaskStatus|Optional $status,
        public readonly TaskPriority|Optional $priority,
        public readonly string|null|Optional $due_date,
        public readonly int|Optional $position,
    ) {
    }

    public function toContentAttributes(): array
    {
        return $this->transformDataValues([
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
        ]);
    }

    public function toMovementAttributes(): array
    {
        return $this->transformDataValues([
            'board_id' => $this->board_id,
            'status' => $this->status,
            'position' => $this->position,
        ]);
    }
}
