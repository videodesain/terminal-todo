<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EditorialCalendar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'editorial_calendar';

    protected $fillable = [
        'title',
        'description',
        'content',
        'category_id',
        'platform_id',
        'team_id',
        'created_by',
        'status',
        'publish_date',
        'published_at',
        'deadline',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'publish_date' => 'datetime',
        'published_at' => 'datetime',
        'deadline' => 'datetime'
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'editorial_calendar_assignees', 'calendar_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(CalendarComment::class, 'calendar_id')
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
