<?php

namespace App\DTO\Auth;

use Spatie\LaravelData\Data;

class ResetPasswordData extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $password_confirmation,
        public readonly string $token,
    ) {
    }

    public function toCredentials(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token,
        ];
    }
}
