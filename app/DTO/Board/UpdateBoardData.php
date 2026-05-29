<?php

namespace App\DTO\Board;

use App\DTO\Concerns\TransformsDataValues;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateBoardData extends Data
{
    use TransformsDataValues;

    public function __construct(
        public readonly string|Optional $name,
        public readonly string|null|Optional $description,
    ) {
    }

    public function toAttributes(): array
    {
        return $this->transformDataValues([
            'name' => $this->name,
            'description' => $this->description,
        ]);
    }
}
