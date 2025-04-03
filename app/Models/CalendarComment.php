<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'calendar_id',
        'user_id',
        'attachment_type',
        'attachment_url',
        'attachment_filename',
        'attachment_file_size',
        'link_url',
        'link_title'
    ];

    protected $casts = [
        'attachment_file_size' => 'integer'
    ];

    /**
     * Get the calendar that owns the comment.
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(EditorialCalendar::class);
    }

    /**
     * Get the user that owns the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attachments for the comment.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(CalendarCommentAttachment::class, 'comment_id');
    }
} 