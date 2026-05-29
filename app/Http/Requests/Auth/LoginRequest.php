<?php

namespace App\Http\Requests\Auth;

use App\DTO\Auth\LoginData;
use App\Http\Requests\DataRequest;

class LoginRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return LoginData::class;
    }
}
