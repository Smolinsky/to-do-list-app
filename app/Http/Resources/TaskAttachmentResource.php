<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'type' => $this->type?->value ?? $this->type,
            'download_url' => route('api.tasks.attachments.download', [
                'task' => $this->task_id,
                'attachment' => $this->id,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
