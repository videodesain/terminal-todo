<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'platform_id',
        'team_id',
        'priority',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'archived_at',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function approvals()
    {
        return $this->hasMany(TaskApproval::class);
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class);
    }

    public function dependentTasks()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'dependent_task_id')
            ->withPivot('type', 'notes')
            ->withTimestamps();
    }

    public function workflow()
    {
        return $this->belongsToMany(Workflow::class, 'task_workflow')
            ->withPivot('current_step', 'step_history')
            ->withTimestamps();
    }

    public function contentBrief()
    {
        return $this->hasOne(ContentBrief::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope untuk mengambil task yang aktif (tidak diarsipkan)
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    // Scope untuk mengambil task yang diarsipkan
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    // Scope untuk mengambil task berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk mengambil task yang perlu diarsipkan (completed/cancelled lebih dari X hari)
    public function scopeNeedsArchiving($query, $days = 30)
    {
        return $query->where(function($q) {
            $q->where('status', 'completed')
              ->orWhere('status', 'cancelled');
        })
        ->whereNull('archived_at')
        ->where(function($q) use ($days) {
            $q->where('completed_at', '<=', now()->subDays($days))
              ->orWhere('updated_at', '<=', now()->subDays($days));
        });
    }
}
