<?php

namespace App\DTO\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Spatie\LaravelData\Data;

class CreateTaskData extends Data
{
    public function __construct(
        public readonly User $user,
        public readonly ?int $board_id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly TaskStatus $status,
        public readonly TaskPriority $priority,
        public readonly ?string $due_date,
        public readonly ?int $position,
    ) {
    }

    public function toTaskAttributes(): array
    {
        return [
            'board_id' => $this->board_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'due_date' => $this->due_date,
            'position' => $this->position,
        ];
    }
}
