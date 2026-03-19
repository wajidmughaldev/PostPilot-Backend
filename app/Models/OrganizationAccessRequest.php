<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationAccessRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requested_organization_name',
        'contact_person_name',
        'contact_email',
        'contact_phone',
        'timezone',
        'website_url',
        'bio',
        'location',
        'industry',
        'organization_size',
        'message',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'organization_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
