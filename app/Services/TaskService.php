<?php

namespace App\Services;

use App\Models\User;

class TaskService
{
    public function createTask(User $user, array $newTaskData)
    {
        return $user->tasks()->create($newTaskData);
    }
}
