<?php

namespace App\Services;

use App\DTO\AuthData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function authenticateByCredentials(string $login, string $password): AuthData
    {
        /** @var User $user */
        $user = User::query()->where('email', $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('validation.credentials_not_correct')]
            ]);
        }

        return AuthData::from([
            'id' => $user->id,
            'accessToken' => $user->createToken('api')->plainTextToken,
        ]);
    }
}
