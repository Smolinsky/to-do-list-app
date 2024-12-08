<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
class AuthData extends Data
{
    /**
     * @param  int  $id
     * @param  string  $accessToken
     */
    public function __construct(
        public readonly int $id,
        public readonly string $accessToken,
    ) {
    }
}
