<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveTasks extends Command
{
    protected $signature = 'tasks:archive';
    protected $description = 'Archive completed and cancelled tasks that are older than 30 days';

    public function handle()
    {
        try {
            $tasksToArchive = Task::needsArchiving()->get();
            $count = $tasksToArchive->count();

            foreach ($tasksToArchive as $task) {
                $task->update(['archived_at' => now()]);
            }

            Log::info("[TASK] Successfully archived {$count} tasks");
            $this->info("Successfully archived {$count} tasks");
        } catch (\Exception $e) {
            Log::error("[TASK] Error archiving tasks: " . $e->getMessage());
            $this->error("Error archiving tasks: " . $e->getMessage());
        }
    }
} 