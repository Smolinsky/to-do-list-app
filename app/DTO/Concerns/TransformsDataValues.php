<?php

namespace App\DTO\Concerns;

use BackedEnum;
use Spatie\LaravelData\Optional;

trait TransformsDataValues
{
    protected function transformDataValues(array $values): array
    {
        return collect($values)
            ->reject(fn(mixed $value) => $value instanceof Optional)
            ->map(fn(mixed $value) => $value instanceof BackedEnum ? $value->value : $value)
            ->all();
    }
}
