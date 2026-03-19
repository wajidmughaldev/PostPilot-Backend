<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'title',
        'content',
        'status',
        'publish_mode',
        'scheduled_at',
        'published_at',
        'timezone',
        'platforms',
        'account_ids',
        'account_labels',
        'media_name',
        'media_type',
        'visibility',
        'age_min',
        'age_max',
        'locations',
        'interests',
        'lookalike_audience',
        'failure_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'platforms' => 'array',
        'account_ids' => 'array',
        'account_labels' => 'array',
        'locations' => 'array',
        'interests' => 'array',
        'lookalike_audience' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
