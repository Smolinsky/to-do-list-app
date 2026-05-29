<?php

namespace App\DTO\Task;

use App\DTO\Concerns\TransformsDataValues;
use App\Enums\TaskStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class MoveTaskData extends Data
{
    use TransformsDataValues;

    public function __construct(
        public readonly int|null|Optional $board_id,
        public readonly TaskStatus|Optional $status,
        public readonly int|Optional $position,
    ) {
    }

    public function toAttributes(): array
    {
        return $this->transformDataValues([
            'board_id' => $this->board_id,
            'status' => $this->status,
            'position' => $this->position,
        ]);
    }
}
