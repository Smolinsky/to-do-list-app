<?php

namespace App\Http\Requests\Board;

use App\DTO\Board\UpdateBoardData;
use App\Http\Requests\DataRequest;

class UpdateBoardRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return UpdateBoardData::class;
    }
}
