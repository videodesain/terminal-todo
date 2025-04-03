<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'platform_id',
        'name',
        'username',
        'account_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'account_type',
        'profile_url',
        'avatar_url',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'token_expires_at' => 'datetime'
    ];

    public function platform()
    {
        return $this->belongsTo(SocialPlatform::class, 'platform_id');
    }

    public function metricData()
    {
        return $this->hasMany(MetricData::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
