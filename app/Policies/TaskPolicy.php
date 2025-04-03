<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // User dapat mengupdate task jika:
        // 1. User adalah creator task
        // 2. User adalah assignee task
        // 3. User memiliki permission manage-task
        return $user->id === $task->created_by ||
               $task->assignees->contains($user->id) ||
               $user->hasPermissionTo('manage-task');
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // User dapat menghapus task jika:
        // 1. User adalah creator task
        // 2. User memiliki permission manage-task
        return $user->id === $task->created_by ||
               $user->hasPermissionTo('manage-task');
    }
} 