<?php

namespace App\Http\Requests\Auth\Registration;

use App\DTO\RegisterData;
use App\Http\Requests\DataRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8', Password::default()],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return RegisterData::class;
    }
}
