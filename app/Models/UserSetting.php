<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme_preference',
        'interface_language',
        'timezone',
        'email_notifications',
        'browser_notifications',
        'marketing_updates',
        'data_sharing_enabled',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'browser_notifications' => 'boolean',
        'marketing_updates' => 'boolean',
        'data_sharing_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
