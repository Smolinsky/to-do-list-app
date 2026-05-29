<?php

namespace App\DTO\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class UploadTaskAttachmentData extends Data
{
    public function __construct(
        public readonly User $user,
        public readonly Task $task,
        public readonly UploadedFile $file,
    ) {
    }
}
