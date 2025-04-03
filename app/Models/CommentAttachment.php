<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'url',
        'filename',
        'file_size',
        'mime_type',
        'title',
        'comment_id'
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
} 