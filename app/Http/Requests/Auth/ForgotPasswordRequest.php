<?php

namespace App\Http\Requests\Auth;

use App\DTO\Auth\ForgotPasswordData;
use App\Http\Requests\DataRequest;

class ForgotPasswordRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return ForgotPasswordData::class;
    }
}
