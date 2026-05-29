<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'board_id' => $this->board_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status?->value ?? $this->status,
            'priority' => $this->priority?->value ?? $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'position' => $this->position,
            'attachments_count' => $this->whenCounted('attachments'),
            'attachments' => TaskAttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
