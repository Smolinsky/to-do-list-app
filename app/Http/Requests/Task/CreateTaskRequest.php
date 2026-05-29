<?php

namespace App\Http\Requests\Task;

use App\DTO\Task\CreateTaskData;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\DataRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'board_id' => [
                'nullable',
                'integer',
                Rule::exists('boards', 'id')->where(fn($query) => $query->where('user_id', $this->user()->id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:' . implode(',', TaskStatus::values())],
            'priority' => ['required', 'string', 'in:' . implode(',', TaskPriority::values())],
            'due_date' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return CreateTaskData::class;
    }

    protected function dtoContext(): array
    {
        return [
            'user' => $this->authenticatedUser(),
        ];
    }
}
