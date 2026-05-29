<?php

namespace App\Http\Controllers\Api;

use App\DTO\Task\UploadTaskAttachmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\UploadTaskAttachmentRequest;
use App\Http\Resources\TaskAttachmentResource;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\TaskAttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    public function __construct(
        private readonly TaskAttachmentService $taskAttachmentService
    ) {
    }

    public function getAttachments(Task $task)
    {
        Gate::authorize('check-task', $task);

        return TaskAttachmentResource::collection(
            $task->attachments()->latest()->get()
        );
    }

    public function uploadAttachment(UploadTaskAttachmentRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('check-task', $task);

        /** @var UploadTaskAttachmentData $attachmentData */
        $attachmentData = $request->getDTO();

        $attachment = $this->taskAttachmentService->uploadAttachment($attachmentData);

        return TaskAttachmentResource::make($attachment)
            ->response()
            ->setStatusCode(201);
    }

    public function downloadAttachment(Task $task, TaskAttachment $attachment): StreamedResponse
    {
        Gate::authorize('check-task', $task);
        abort_if($attachment->task_id !== $task->id, 404);

        return $this->taskAttachmentService->downloadAttachment($attachment);
    }

    public function deleteAttachment(Task $task, TaskAttachment $attachment): JsonResponse
    {
        Gate::authorize('check-task', $task);
        abort_if($attachment->task_id !== $task->id, 404);

        $this->taskAttachmentService->deleteAttachment($attachment);

        return response()->json([
            'message' => 'Successfully deleted',
        ]);
    }
}
