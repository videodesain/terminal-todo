<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarCommentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'type',
        'url',
        'filename',
        'file_size',
        'mime_type',
        'title'
    ];

    protected $casts = [
        'file_size' => 'integer'
    ];

    /**
     * Get the comment that owns the attachment.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(CalendarComment::class, 'comment_id');
    }
} 