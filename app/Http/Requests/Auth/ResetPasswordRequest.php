<?php

namespace App\Http\Requests\Auth;

use App\DTO\Auth\ResetPasswordData;
use App\Http\Requests\DataRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::default()],
            'token' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return ResetPasswordData::class;
    }
}
