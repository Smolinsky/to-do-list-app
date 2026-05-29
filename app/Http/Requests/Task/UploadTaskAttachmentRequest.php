<?php

namespace App\Http\Requests\Task;

use App\DTO\Task\UploadTaskAttachmentData;
use App\Http\Requests\DataRequest;
use App\Models\Task;

class UploadTaskAttachmentRequest extends DataRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function dataClass(): string
    {
        return UploadTaskAttachmentData::class;
    }

    protected function dtoContext(): array
    {
        /** @var Task $task */
        $task = $this->route('task');

        return [
            'user' => $this->authenticatedUser(),
            'task' => $task,
        ];
    }
}
