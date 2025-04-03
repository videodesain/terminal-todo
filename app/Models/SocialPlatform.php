<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialPlatform extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        'api_key',
        'api_secret',
        'is_active',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class, 'platform_id');
    }
} 