<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

class RegisterData extends Data
{
    /**
     * @param  string  $name
     * @param  string  $email
     * @param  string  $password
     * @param  string|null  $accessToken
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $accessToken = null,
    ) {
    }
}
