<?php

namespace App\Services;

use App\DTO\Task\UploadTaskAttachmentData;
use App\Enums\AttachmentType;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentService
{
    public function uploadAttachment(UploadTaskAttachmentData $attachmentData): TaskAttachment
    {
        $file = $attachmentData->file;
        $task = $attachmentData->task;

        $storedName = Str::uuid().($file->getClientOriginalExtension()
            ? '.'.$file->getClientOriginalExtension()
            : '');

        $disk = 'local';
        $path = $file->storeAs(
            "tasks/{$task->id}/attachments",
            $storedName,
            $disk
        );

        return $task->attachments()->create([
            'user_id' => $attachmentData->user->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'type' => Str::startsWith($file->getMimeType() ?? '', 'image/')
                ? AttachmentType::IMAGE
                : AttachmentType::FILE,
        ]);
    }

    public function downloadAttachment(TaskAttachment $attachment): StreamedResponse
    {
        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    public function deleteAttachment(TaskAttachment $attachment): void
    {
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();
    }
}
