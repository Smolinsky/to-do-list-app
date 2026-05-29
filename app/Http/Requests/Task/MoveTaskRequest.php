<?php

namespace App\Http\Requests\Task;

use App\DTO\Task\MoveTaskData;
use App\Enums\TaskStatus;
use App\Http\Requests\DataRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'board_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('boards', 'id')->where(fn($query) => $query->where('user_id', $this->user()->id)),
            ],
            'status' => ['sometimes', 'string', 'in:' . implode(',', TaskStatus::values())],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return MoveTaskData::class;
    }
}
