<?php

namespace App\DTO\Auth;

use Spatie\LaravelData\Data;

class LoginData extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
