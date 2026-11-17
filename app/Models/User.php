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
        'subscription_tier',
        'subscription_plan',
        'subscription_expires_at',
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
            'subscription_expires_at' => 'datetime',
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

    public function isPro(): bool
    {
        if (strtolower($this->subscription_tier ?? '') !== 'pro') {
            return false;
        }

        return $this->subscription_expires_at === null || $this->subscription_expires_at->isFuture();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'profile_picture' => $this->profile_picture,
            'subscription_tier' => $this->subscription_tier ?? 'free',
            'subscription_plan' => $this->subscription_plan,
            'subscription_expires_at' => $this->subscription_expires_at?->toIso8601String(),
            'is_pro' => $this->isPro(),
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
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
