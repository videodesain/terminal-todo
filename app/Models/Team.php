<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'color',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            $team->slug = static::generateUniqueSlug($team->name);
            if (!$team->color) {
                $team->color = static::generateRandomColor();
            }
        });

        static::updating(function ($team) {
            if ($team->isDirty('name')) {
                $team->slug = static::generateUniqueSlug($team->name);
            }
        });
    }

    /**
     * Generate a unique slug for the team.
     */
    protected static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    protected static function generateRandomColor()
    {
        $colors = [
            '#3B82F6', // blue
            '#8B5CF6', // purple
            '#EC4899', // pink
            '#10B981', // green
            '#F59E0B', // yellow
            '#EF4444', // red
            '#6366F1', // indigo
            '#14B8A6', // teal
            '#F97316', // orange
            '#6B7280'  // gray
        ];
        return $colors[array_rand($colors)];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the tasks that belong to the team.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the editorial calendars that belong to the team.
     */
    public function editorialCalendars()
    {
        return $this->hasMany(EditorialCalendar::class);
    }

    /**
     * Get the user who created the team.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
