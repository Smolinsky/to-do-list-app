<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskAttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanUploadFileAttachmentToTask(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('specification.pdf', 128, 'application/pdf');

        $this->actingAs($user)
            ->postJson(route('api.tasks.attachments.create', $task), [
                'file' => $file,
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'task_id' => $task->id,
                'original_name' => 'specification.pdf',
                'type' => 'file',
            ]);

        $attachment = TaskAttachment::query()->firstOrFail();

        Storage::disk('local')->assertExists($attachment->path);
    }

    public function testUserCanUploadImageAttachmentToTask(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->image('screenshot.png');

        $this->actingAs($user)
            ->postJson(route('api.tasks.attachments.create', $task), [
                'file' => $file,
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'type' => 'image',
            ]);
    }

    public function testUserCanListTaskAttachments(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        TaskAttachment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'first.txt',
            'stored_name' => 'first.txt',
            'path' => 'tasks/'.$task->id.'/attachments/first.txt',
            'disk' => 'local',
            'mime_type' => 'text/plain',
            'size' => 100,
            'type' => 'file',
        ]);

        TaskAttachment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'second.txt',
            'stored_name' => 'second.txt',
            'path' => 'tasks/'.$task->id.'/attachments/second.txt',
            'disk' => 'local',
            'mime_type' => 'text/plain',
            'size' => 200,
            'type' => 'file',
        ]);

        $this->actingAs($user)
            ->getJson(route('api.tasks.attachments', $task))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function testUserCanDeleteAttachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $path = 'tasks/'.$task->id.'/attachments/delete-me.txt';
        Storage::disk('local')->put($path, 'attachment');

        $attachment = TaskAttachment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'delete-me.txt',
            'stored_name' => 'delete-me.txt',
            'path' => $path,
            'disk' => 'local',
            'mime_type' => 'text/plain',
            'size' => 10,
            'type' => 'file',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('api.tasks.attachments.delete', [
                'task' => $task,
                'attachment' => $attachment,
            ]))
            ->assertOk()
            ->assertJson([
                'message' => 'Successfully deleted',
            ]);

        $this->assertDatabaseMissing('task_attachments', [
            'id' => $attachment->id,
        ]);

        Storage::disk('local')->assertMissing($path);
    }

    public function testUserCanDownloadAttachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $path = 'tasks/'.$task->id.'/attachments/specification.txt';
        Storage::disk('local')->put($path, 'attachment-content');

        $attachment = TaskAttachment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'specification.txt',
            'stored_name' => 'specification.txt',
            'path' => $path,
            'disk' => 'local',
            'mime_type' => 'text/plain',
            'size' => 18,
            'type' => 'file',
        ]);

        $this->actingAs($user)
            ->get(route('api.tasks.attachments.download', [
                'task' => $task,
                'attachment' => $attachment,
            ]))
            ->assertOk()
            ->assertHeader(
                'content-disposition',
                'attachment; filename=specification.txt'
            );
    }

    public function testDownloadReturnsNotFoundWhenAttachmentBelongsToAnotherTask(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);
        $anotherTask = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $path = 'tasks/'.$anotherTask->id.'/attachments/another.txt';
        Storage::disk('local')->put($path, 'attachment-content');

        $attachment = TaskAttachment::query()->create([
            'task_id' => $anotherTask->id,
            'user_id' => $user->id,
            'original_name' => 'another.txt',
            'stored_name' => 'another.txt',
            'path' => $path,
            'disk' => 'local',
            'mime_type' => 'text/plain',
            'size' => 18,
            'type' => 'file',
        ]);

        $this->actingAs($user)
            ->get(route('api.tasks.attachments.download', [
                'task' => $task,
                'attachment' => $attachment,
            ]))
            ->assertNotFound();
    }

    public function testUserCannotDeleteAnotherUsersAttachment(): void
    {
        Storage::fake('local');

        $taskOwner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $taskOwner->id,
        ]);

        $path = 'tasks/'.$task->id.'/attachments/owner-file.txt';
        Storage::disk('local')->put($path, 'attachment');

        $attachment = TaskAttachment::query()->create([
            'task_id' => $task->id,
            'user_id' => $taskOwner->id,
            'original_name' => 'owner-file.txt',
            'stored_name' => 'owner-file.txt',
            'path' => $path,
            'disk' => 'local',
            'mime_type' => 'text/plain',
            'size' => 10,
            'type' => 'file',
        ]);

        $this->actingAs($anotherUser)
            ->deleteJson(route('api.tasks.attachments.delete', [
                'task' => $task,
                'attachment' => $attachment,
            ]))
            ->assertForbidden();
    }
}
