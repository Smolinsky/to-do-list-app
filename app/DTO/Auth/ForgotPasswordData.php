<?php

namespace App\DTO\Auth;

use Spatie\LaravelData\Data;

class ForgotPasswordData extends Data
{
    public function __construct(
        public readonly string $email,
    ) {
    }

    public function toCredentials(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
