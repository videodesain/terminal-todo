<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    /**
     * Mendapatkan semua tasks dengan relasi yang diperlukan
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTasks()
    {
        return Task::with(['category', 'platform', 'team', 'assignees', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mendapatkan task berdasarkan status
     *
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasksByStatus($status)
    {
        return Task::with(['category', 'platform', 'team', 'assignees', 'creator'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mendapatkan task berdasarkan ID
     *
     * @param int $id
     * @return Task|null
     */
    public function getTaskById($id)
    {
        return Task::with(['category', 'platform', 'team', 'assignees', 'creator', 'comments'])
            ->find($id);
    }

    /**
     * Membuat task baru
     *
     * @param array $data
     * @param User $user
     * @return Task
     */
    public function createTask(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Buat task
            $task = new Task();
            $task->title = $data['title'];
            $task->description = $data['description'] ?? null;
            $task->category_id = $data['category_id'] ?? null;
            $task->platform_id = $data['platform_id'] ?? null;
            $task->team_id = $data['team_id'] ?? null;
            $task->created_by = $user->id;
            $task->priority = $data['priority'];
            $task->status = $data['status'];
            $task->start_date = $data['start_date'] ?? null;
            $task->due_date = $data['due_date'] ?? null;
            $task->save();

            // Tambahkan assignees
            if (!empty($data['assignee_ids'])) {
                $task->assignees()->attach($data['assignee_ids']);
            }

            // Clear cache
            $this->clearTaskCache();

            return $task;
        });
    }

    /**
     * Memperbarui task
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function updateTask(Task $task, array $data)
    {
        return DB::transaction(function () use ($task, $data) {
            // Update task
            $task->title = $data['title'];
            $task->description = $data['description'] ?? null;
            $task->category_id = $data['category_id'] ?? null;
            $task->platform_id = $data['platform_id'] ?? null;
            $task->team_id = $data['team_id'] ?? null;
            $task->priority = $data['priority'];
            $task->status = $data['status'];
            $task->start_date = $data['start_date'] ?? null;
            $task->due_date = $data['due_date'] ?? null;
            $task->save();

            // Update assignees
            if (isset($data['assignee_ids'])) {
                $task->assignees()->sync($data['assignee_ids']);
            }

            // Clear cache
            $this->clearTaskCache();

            return $task;
        });
    }

    /**
     * Memperbarui status task
     *
     * @param Task $task
     * @param string $status
     * @return Task
     */
    public function updateTaskStatus(Task $task, string $status)
    {
        return DB::transaction(function () use ($task, $status) {
            $task->status = $status;
            
            // Jika status completed, update completed_at
            if ($status === 'completed' && !$task->completed_at) {
                $task->completed_at = now();
            }
            
            $task->save();
            
            // Clear cache
            $this->clearTaskCache();
            
            return $task;
        });
    }

    /**
     * Menghapus task
     *
     * @param Task $task
     * @return bool
     */
    public function deleteTask(Task $task)
    {
        return DB::transaction(function () use ($task) {
            // Hapus assignees
            $task->assignees()->detach();
            
            // Hapus task
            $result = $task->delete();
            
            // Clear cache
            $this->clearTaskCache();
            
            return $result;
        });
    }

    /**
     * Clear task cache
     */
    public function clearTaskCache()
    {
        Cache::forget('tasks.all');
        Cache::forget('tasks.collection');
    }
} 