<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Platform extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($platform) {
            $platform->slug = static::generateUniqueSlug($platform->name);
        });

        static::updating(function ($platform) {
            if ($platform->isDirty('name')) {
                $platform->slug = static::generateUniqueSlug($platform->name);
            }
        });
    }

    protected static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Relationships
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function contentTemplates()
    {
        return $this->hasMany(ContentTemplate::class);
    }

    public function editorialCalendars()
    {
        return $this->hasMany(EditorialCalendar::class);
    }
}
