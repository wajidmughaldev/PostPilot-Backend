<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'avatar_path',
        'email',
        'password',
        'platform_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function organizationMembers(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function organizationAccessRequests(): HasMany
    {
        return $this->hasMany(OrganizationAccessRequest::class);
    }

    public function createdOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    public function reviewedOrganizationAccessRequests(): HasMany
    {
        return $this->hasMany(OrganizationAccessRequest::class, 'reviewed_by');
    }

    public function organizationInvites(): HasMany
    {
        return $this->hasMany(OrganizationInvite::class, 'invited_by');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->platform_role === 'super_admin';
    }
}
