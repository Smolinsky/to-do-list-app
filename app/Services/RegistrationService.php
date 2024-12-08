<?php

namespace App\Services;

use App\DTO\RegisterData;
use App\Events\RegisteredEvent;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    public function registerUser(RegisterData $registerData): RegisterData
    {
        $user = User::create([
            'name' => $registerData->name,
            'email' => $registerData->email,
            'password' => Hash::make($registerData->password),
        ]);

        $accessToken = $user->createToken('api')->plainTextToken;

        event(new RegisteredEvent($user));

        return new RegisterData(
            $user->name,
            $user->email,
            $registerData->password,
            $accessToken,
        );
    }
}
