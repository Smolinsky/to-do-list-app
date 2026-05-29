<?php

namespace App\DTO\Board;

use App\Models\User;
use Spatie\LaravelData\Data;

class CreateBoardData extends Data
{
    public function __construct(
        public readonly User $user,
        public readonly string $name,
        public readonly ?string $description,
    ) {
    }

    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
