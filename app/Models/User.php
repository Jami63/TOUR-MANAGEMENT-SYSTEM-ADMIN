<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'role',
        'profile_picture',
        'notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_enabled' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Tours created by this user (Manager).
     */
    public function createdTours(): HasMany
    {
        return $this->hasMany(Tour::class, 'created_by');
    }

    /**
     * Tour memberships.
     */
    public function tourMemberships(): HasMany
    {
        return $this->hasMany(TourMember::class, 'user_id');
    }

    /**
     * Tours joined by this user.
     */
    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'tour_members', 'user_id', 'tour_id')
            ->withPivot(['id', 'tour_role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Notifications received.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Expenses added by this user.
     */
    public function addedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'added_by');
    }

    /**
     * Payments verified by this manager.
     */
    public function paymentVerifications(): HasMany
    {
        return $this->hasMany(PaymentVerification::class, 'verified_by');
    }
}
