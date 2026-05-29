<?php

namespace App\Http\Requests\Board;

use App\DTO\Board\CreateBoardData;
use App\Http\Requests\DataRequest;

class CreateBoardRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return CreateBoardData::class;
    }

    protected function dtoContext(): array
    {
        return [
            'user' => $this->authenticatedUser(),
        ];
    }
}
